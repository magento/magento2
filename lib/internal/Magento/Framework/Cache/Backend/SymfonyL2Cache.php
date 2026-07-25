<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Exception\CacheException;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Magento\Framework\Cache\FrontendInterface;

/**
 * L2 (Two-Level) Cache Backend for Symfony Adapters
 *
 * This backend provides local + remote caching with automatic synchronization,
 * designed specifically for Symfony cache adapters (PSR-6 compliant).
 *
 * This class works directly with Symfony's FrontendInterface and does not require
 * ExtendedBackendInterface.
 *
 * Architecture:
 * - L1 (Local): Fast cache (file/APCu) - Per worker, ephemeral
 * - L2 (Remote): Persistent cache (Redis/Valkey) - Shared, persistent
 * - Sync: :hash mechanism detects stale local data
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SymfonyL2Cache extends AbstractBackend implements ExtendedBackendInterface
{
    /**
     * Local backend cache (L1)
     *
     * @var FrontendInterface
     */
    private FrontendInterface $local;

    /**
     * Remote backend cache (L2)
     *
     * @var FrontendInterface
     */
    private FrontendInterface $remote;

    /**
     * Suffix for hash to compare data version in cache storage
     */
    private const HASH_SUFFIX = ':hash';

    /**
     * Default cleanup percentage for L1 cache
     */
    private const DEFAULT_CLEANUP_PERCENTAGE = 90;

    /**
     * Cleanup percentage threshold (when to trigger L1 cleanup)
     *
     * @var int
     */
    private int $cleanupPercentage;

    /**
     * Whether to use stale cache when remote (L2) is unavailable
     *
     * @var bool
     */
    private bool $useStaleCache;

    /**
     * Key prefix for tracking invalid entries in local cache
     */
    private const INVALID_KEY_PREFIX = '__invalid::';

    /**
     * TTL for invalid markers for 24 hours
     */
    private const INVALID_MARK_TTL = 86400;

    /**
     * Regeneration lock key prefix (stored in remote L2)
     */
    private const LOCK_PREFIX = '___stale_regen_lock_';

    /**
     * Regeneration lock lifetime in seconds (auto-expires if a regenerator dies)
     */
    private const LOCK_TTL = 10;

    /**
     * Per-process signature to confirm regeneration-lock ownership
     *
     * @var string
     */
    private string $lockSign;

    /**
     * Resolved Redis tag adapter from the remote frontend, used for atomic lock ops.
     * Null once resolution has run and the remote is not Redis-backed.
     *
     * @var RedisTagAdapter|null
     */
    private ?RedisTagAdapter $lockAdapter = null;

    /**
     * Whether lock-adapter resolution has been attempted (so a null result is not re-resolved)
     *
     * @var bool
     */
    private bool $lockAdapterResolved = false;

    /**
     * Cache ids for which this process currently holds the regeneration lock, so save() only
     * issues a release round-trip for locks it actually owns.
     *
     * @var array<string, true>
     */
    private array $heldLocks = [];

    /**
     * Constructor
     *
     * @param FrontendInterface $remote Remote cache (L2 - persistent, shared)
     * @param FrontendInterface $local Local cache (L1 - fast, per-worker)
     * @param array $options Additional options
     * @throws CacheException
     */
    public function __construct(
        FrontendInterface $remote,
        FrontendInterface $local,
        array $options = []
    ) {
        parent::__construct($options);

        $this->remote = $remote;
        $this->local = $local;
        $this->cleanupPercentage = (int)($options['cleanup_percentage'] ?? self::DEFAULT_CLEANUP_PERCENTAGE);
        $this->useStaleCache = (bool)($options['use_stale_cache'] ?? false);
        $this->lockSign = $this->generateLockSign();

        // Validate cleanup percentage
        if ($this->cleanupPercentage < 1 || $this->cleanupPercentage > 100) {
            throw new CacheException(__('cleanup_percentage must be between 1 and 100'));
        }
    }

    /**
     * @inheritDoc
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        // Try local cache first (fast path)
        $localData = $this->local->load($id);

        if ($this->isInvalid($id)) {
            return $this->handleInvalidKey($id);
        }

        if ($localData !== false) {
            $result = $this->validateLocalCache($id, $localData);
            if ($result !== null) {
                return $result;
            }
            // Local cache is stale, fall through to load from remote
        }

        return $this->loadFromRemoteOrFallback($id, $localData);
    }

    /**
     * @inheritDoc
     */
    public function test($id)
    {
        if ($this->useStaleCache) {
            // With stale cache, check local first for availability
            return $this->local->test($id) ?: $this->remote->test($id);
        }

        // Check remote cache (source of truth)
        return $this->remote->test($id);
    }

    /**
     * @inheritDoc
     */
    public function save($data, $id, $tags = [], $specificLifetime = null)
    {
        $hashSaved = false;

        try {
            // Save data first to avoid hash pointing to non-existent data
            $remoteSaved = $this->remote->save($data, $id, $tags, $specificLifetime);

            if ($remoteSaved !== false) {
                // Calculate and save hash to remote for synchronization
                $hash = $this->getDataHash($data);
                $hashSaved = $this->remote->save($hash, $id . self::HASH_SUFFIX, $tags, $specificLifetime);
            }
        } catch (\Exception $e) {
            $remoteSaved = false;
            $hashSaved = false;
        }

        // Save to local cache
        $this->local->save($data, $id, $tags, $specificLifetime);

        if ($remoteSaved !== false && $hashSaved !== false) {
            $this->markValid($id);
        } else {
            if ($this->useStaleCache) {
                $this->markInvalid($id);
            }
        }

        // If this process elected itself the regenerator for $id, the fresh value is now in L2,
        // so release the lock immediately (ownership-safe) instead of leaving it to the TTL. This
        // lets an immediate re-invalidation elect a new regenerator right away rather than serving
        // stale until LOCK_TTL expires.
        $this->releaseRegenLock($id);

        return $remoteSaved;
    }

    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        try {
            // Remove hash from remote
            $hashRemoved = $this->remote->remove($id . self::HASH_SUFFIX);

            // Remove from remote
            $result = $this->remote->remove($id);
        } catch (\Exception $e) {
            $hashRemoved = false;
            $result = false;
        }

        // Only remove from local if NOT using stale cache (keep stale data for availability)
        if (!$this->useStaleCache) {
            $this->local->remove($id);
        }

        if ($result !== false && $hashRemoved !== false) {
            $this->markValid($id);
        } else {
            if ($this->useStaleCache) {
                $this->markInvalid($id);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, $tags = [])
    {
        // Clean both caches
        $this->local->clean($mode, $tags);
        return $this->remote->clean($mode, $tags);
    }

    /**
     * Calculate hash of data for synchronization
     *
     * @param string $data
     * @return string
     */
    private function getDataHash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * @inheritDoc
     */
    public function getIds()
    {
        // Return IDs from remote (source of truth)
        // Note: This may not be supported by all Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTags()
    {
        // Return tags from remote
        // Note: This may not be supported by all Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsMatchingTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getFillingPercentage()
    {
        // Cannot determine filling percentage for L2 cache
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getMetadatas($id)
    {
        // Get test result (timestamp)
        $mtime = $this->remote->test($id);

        if ($mtime === false) {
            return false;
        }

        return [
            'expire' => null,
            'tags' => [],
            'mtime' => $mtime,
        ];
    }

    /**
     * @inheritDoc
     */
    public function touch($id, $extraLifetime)
    {
        // Reload and resave with extended lifetime
        $data = $this->remote->load($id);

        if ($data === false) {
            return false;
        }

        return $this->save($data, $id, [], $extraLifetime);
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities()
    {
        return [
            'automatic_cleaning' => false,
            'tags' => true,
            'expired_read' => false,
            'priority' => false,
            'infinite_lifetime' => true,
            'get_list' => false,
        ];
    }

    /**
     * Get remote cache frontend
     *
     * @return FrontendInterface
     */
    public function getRemote(): FrontendInterface
    {
        return $this->remote;
    }

    /**
     * Get local cache frontend
     *
     * @return FrontendInterface
     */
    public function getLocal(): FrontendInterface
    {
        return $this->local;
    }

    /**
     * Check if a cache key was modified while remote was unavailable
     *
     * @param string $id
     * @return bool
     */
    private function isInvalid(string $id): bool
    {
        return $this->local->load(self::INVALID_KEY_PREFIX . $id) !== false;
    }

    /**
     * Mark a cache key as invalid (modified while remote was unavailable)
     *
     * @param string $id
     * @return void
     */
    private function markInvalid(string $id): void
    {
        $this->local->save('1', self::INVALID_KEY_PREFIX . $id, [], self::INVALID_MARK_TTL);
    }

    /**
     * Mark a cache key as valid (synchronized with remote)
     *
     * Only removes the L1 invalid marker when one actually exists. Markers are written without
     * tags and only when a remote write fails, so on the healthy path (e.g. post-deploy cache
     * warmup, remote up) there is nothing to clear. Skipping avoids an L1 remove() — and the
     * FilesystemTagAdapter::onRemove() it triggers — on every successful save.
     *
     * @param string $id
     * @return void
     */
    private function markValid(string $id): void
    {
        if ($this->isInvalid($id)) {
            $this->local->remove(self::INVALID_KEY_PREFIX . $id);
        }
    }

    /**
     * Clean an invalid key from remote cache
     *
     * @param string $id
     * @return bool
     */
    private function cleanInvalidFromRemote(string $id): bool
    {
        try {
            $this->remote->remove($id . self::HASH_SUFFIX);
            $this->remote->remove($id);
            return true;
        } catch (\Exception $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // If remote is still unavailable, the invalid marker will be cleared anyway
            return false;
        }
    }

    /**
     * Handle invalid key by cleaning from remote and local
     *
     * @param string $id
     * @return false
     */
    private function handleInvalidKey(string $id)
    {
        $remoteCleanSuccess = $this->cleanInvalidFromRemote($id);
        $this->local->remove($id);

        if ($remoteCleanSuccess) {
            $this->markValid($id);
        }
        return false;
    }

    /**
     * Validate local cache data against remote hash
     *
     * @param string $id
     * @param string $localData
     * @return string|false|null Returns data if valid, false if invalid, null if stale (should try remote)
     */
    private function validateLocalCache(string $id, string $localData)
    {
        $remoteHash = $this->remote->load($id . self::HASH_SUFFIX);

        if ($remoteHash === false && $this->useStaleCache) {
            // Elect one regenerator: lock winner returns a miss (rebuilds + repopulates L2),
            // others serve stale L1 without waiting.
            if ($this->tryLock($id)) {
                return false;
            }
            return $localData;
        }

        $localHash = $this->getDataHash($localData);

        if ($remoteHash === $localHash) {
            return $localData;
        }

        return null;
    }

    /**
     * Try to acquire the non-blocking regeneration lock; returns true for exactly one reader.
     *
     * Uses an atomic SET NX EX on the remote Redis client when available, so exactly one caller
     * cluster-wide wins the election. Falls back to a best-effort (non-atomic) scheme only when
     * the remote is not Redis-backed.
     *
     * @param string $id
     * @return bool
     */
    private function tryLock(string $id): bool
    {
        $adapter = $this->getLockAdapter();

        if ($adapter !== null) {
            if ($adapter->acquireLock($id, $this->lockSign, self::LOCK_TTL)) {
                $this->heldLocks[$id] = true;
                return true;
            }
            return false;
        }

        return $this->tryLockFallback($id);
    }

    /**
     * Release the regeneration lock for $id if this process acquired it (ownership-safe, no-op
     * otherwise). Cheap: only issues a call when this process is recorded as the lock holder.
     *
     * @param string $id
     * @return void
     */
    private function releaseRegenLock(string $id): void
    {
        if (!isset($this->heldLocks[$id])) {
            return;
        }

        $adapter = $this->getLockAdapter();
        if ($adapter !== null) {
            $adapter->releaseLock($id, $this->lockSign);
        }
        unset($this->heldLocks[$id]);
    }

    /**
     * Best-effort, non-atomic lock used only when the remote is not Redis-backed.
     *
     * @param string $id
     * @return bool
     */
    private function tryLockFallback(string $id): bool
    {
        $lockKey = self::LOCK_PREFIX . $id;

        if ($this->remote->load($lockKey) !== false) {
            return false;
        }

        $this->remote->save($this->lockSign, $lockKey, [], self::LOCK_TTL);

        if ($this->remote->load($lockKey) === $this->lockSign) {
            $this->heldLocks[$id] = true;
            return true;
        }

        return false;
    }

    /**
     * Lazily resolve the remote frontend's Redis tag adapter for atomic lock operations.
     *
     * Reaches through any frontend decorators via getLowLevelFrontend(); returns null (once)
     * when the remote is not Redis-backed, in which case the best-effort fallback is used.
     *
     * @return RedisTagAdapter|null
     */
    private function getLockAdapter(): ?RedisTagAdapter
    {
        if ($this->lockAdapterResolved) {
            return $this->lockAdapter;
        }

        $this->lockAdapterResolved = true;

        try {
            if (method_exists($this->remote, 'getLowLevelFrontend')) {
                $lowLevel = $this->remote->getLowLevelFrontend();
                if (method_exists($lowLevel, 'getTagAdapter')) {
                    $adapter = $lowLevel->getTagAdapter();
                    if ($adapter instanceof RedisTagAdapter) {
                        $this->lockAdapter = $adapter;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->lockAdapter = null;
        }

        return $this->lockAdapter;
    }

    /**
     * Generate a unique per-process lock signature (pid-host-random) so lock ownership
     * is unambiguous across servers.
     *
     * @return string
     */
    private function generateLockSign(): string
    {
        $sign = implode('-', [getmypid(), crc32((string)gethostname())]);

        try {
            $sign .= '-' . bin2hex(random_bytes(4));
        } catch (\Exception $e) {
            $sign .= '-' . uniqid('-uniqid-');
        }

        return $sign;
    }

    /**
     * Load from remote cache or fallback to stale local data
     *
     * @param string $id
     * @param string|false $localData
     * @return string|false
     */
    private function loadFromRemoteOrFallback(string $id, $localData)
    {
        $remoteData = $this->remote->load($id);

        if ($remoteData !== false) {
            $this->local->save($remoteData, $id);
            return $remoteData;
        }

        if ($localData && $this->useStaleCache) {
            return $localData;
        }

        return false;
    }
}
