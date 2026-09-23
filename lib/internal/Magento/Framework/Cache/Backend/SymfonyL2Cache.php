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
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     * Absolute path of the L1 (file) cache directory, used to gauge disk fill for size-based
     * eviction. Null when the L1 is not file-backed (then eviction is disabled).
     *
     * @var string|null
     */
    private ?string $localCacheDir;

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
        $this->localCacheDir = isset($options['local_cache_dir']) ? (string)$options['local_cache_dir'] : null;
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
     * Batched multi-load from the remote (L2) tier in a single round-trip.
     *
     * Used by the preloading wrapper to warm a set of hot keys at once, mirroring the legacy Redis
     * wrapper (which pipelines the preload from the shared/slave tier). The remote is the source of
     * truth, so we fetch the values there in one call; per-id L1/hash validation is intentionally
     * skipped for this warm-up path (any preloaded value is still re-validated on a normal load()).
     *
     * @param string[] $ids
     * @return array<string, mixed>
     */
    public function loadMultiple(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        if (method_exists($this->remote, 'loadMultiple')) {
            return $this->remote->loadMultiple($ids);
        }
        $out = [];
        foreach ($ids as $id) {
            $value = $this->remote->load($id);
            if ($value !== false) {
                $out[$id] = $value;
            }
        }
        return $out;
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
            if ($this->isRemoteUpToDate($data, $id)) {
                // L2 already holds this exact value — skip the redundant data+hash write. Mirrors the
                // legacy RemoteSynchronizedCache, which compares the stored hash (and data) before
                // re-writing, so a repeated identical save (cache warmup, re-render of unchanged block
                // output, config re-cache) does not hammer Redis with duplicate writes.
                $remoteSaved = true;
                $hashSaved = true;
            } else {
                // Save data first to avoid hash pointing to non-existent data
                $remoteSaved = $this->remote->save($data, $id, $tags, $specificLifetime);

                if ($remoteSaved !== false) {
                    // Calculate and save hash to remote for synchronization
                    $hash = $this->getDataHash($data);
                    $hashSaved = $this->remote->save($hash, $id . self::HASH_SUFFIX, $tags, $specificLifetime);
                }
            }
        } catch (\Exception $e) {
            $remoteSaved = false;
            $hashSaved = false;
        }

        // Disk-full safety valve: occasionally flush the whole L1 when its partition is nearly full,
        // BEFORE re-saving this entry so the current value survives the flush. Mirrors legacy
        // RemoteSynchronizedCache::save(), which probabilistically calls local->clean() when the L1
        // filling percentage reaches cleanup_percentage. clearLocal() is used (not local->clean(),
        // which is tag-scoped and cannot see an index_tags=false L1).
        if ($this->shouldCheckLocalSpace() && $this->isLocalCacheSpaceExceeded()) {
            $this->clearLocal();
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
        if ($mode === CacheConstants::CLEANING_MODE_ALL) {
            // Full flush: clear the L1 pool directly. A tag-scoped clean() cannot reach the local
            // tier when index_tags=false (it has no tag index), so cache:flush would otherwise leave
            // stale L1 data behind. Mirrors the legacy RemoteSynchronizedCache, whose clean(ALL)
            // clears the raw local backend.
            $this->clearLocal();
            return $this->remote->clean($mode, $tags);
        }
        if ($mode === CacheConstants::CLEANING_MODE_OLD) {
            // TTL garbage collection (run by the backend_clean_cache cron). Prune expired entries from
            // BOTH tiers, mirroring legacy clean(OLD). The L1 FilesystemAdapter physically deletes
            // expired files here; a tag-scoped local->clean() cannot (index_tags=false), so we prune the
            // local backend directly. The remote's clean(OLD) sweeps the Redis tag index (data keys
            // auto-expire via native TTL).
            $this->pruneLocal();
            return $this->remote->clean($mode, $tags);
        }
        $this->local->clean($mode, $tags);
        return $this->remote->clean($mode, $tags);
    }

    /**
     * Prune expired entries from the local (L1) tier when its backend supports pruning.
     *
     * @return void
     */
    private function pruneLocal(): void
    {
        $localBackend = $this->local->getBackend();
        if (method_exists($localBackend, 'prune')) {
            $localBackend->prune();
        }
    }

    /**
     * Fully wipe both cache tiers (L1 + L2). Used by FlushAll / cache:flush via getBackend()->clear().
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->clearLocal();
        return (bool)$this->remote->clean(CacheConstants::CLEANING_MODE_ALL);
    }

    /**
     * Empty the local (L1) tier completely.
     *
     * Bypasses the tag-scoped clean() that cannot see an index_tags=false file index.
     *
     * @return void
     */
    private function clearLocal(): void
    {
        $localBackend = $this->local->getBackend();
        if (method_exists($localBackend, 'clear')) {
            $localBackend->clear();
        } else {
            $this->local->clean(CacheConstants::CLEANING_MODE_ALL);
        }
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
     * Whether the remote (L2) already holds exactly this value, so a re-write would be redundant.
     *
     * Compares the stored :hash first (cheap) and, only on a match, confirms the data itself is still
     * present and identical — guarding against the data having been evicted while the hash lingered
     * under a different TTL. Mirrors legacy RemoteSynchronizedCache::save()'s up-to-date check.
     *
     * @param string $data
     * @param string $id
     * @return bool
     */
    private function isRemoteUpToDate(string $data, string $id): bool
    {
        $remoteHash = $this->remote->load($id . self::HASH_SUFFIX);
        if ($remoteHash === false || $remoteHash !== $this->getDataHash($data)) {
            return false;
        }
        $remoteData = $this->remote->load($id);
        return $remoteData !== false && $remoteData === $data;
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
     *
     * Real remote (L2) storage filling percentage, matching legacy RemoteSynchronizedCache semantics
     * (delegates to the remote backend, e.g. Cm_Cache_Backend_Redis::getFillingPercentage() computing
     * used_memory/maxmemory). Kept distinct from the local L1 disk safety valve in
     * getLocalFillingPercentage() — the two were separate public/private checks in legacy and
     * collapsing them here would silently swap remote memory pressure for local disk usage in any
     * caller of this public API. Returns 0 if the remote adapter can't report it (e.g. non-Redis tag
     * adapter, or the remote is unavailable).
     */
    public function getFillingPercentage()
    {
        try {
            return $this->remote->getLowLevelFrontend()->getTagAdapter()->getFillingPercentage();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Disk-partition filling percentage of the L1 (file) cache dir, matching the legacy Zend file
     * backend semantics (used only as a disk-full safety valve). Returns 0 when the L1 is not
     * file-backed or the dir is unavailable, so eviction never triggers spuriously.
     *
     * @return int
     */
    private function getLocalFillingPercentage(): int
    {
        if ($this->localCacheDir === null || !is_dir($this->localCacheDir)) {
            return 0;
        }

        $free = @disk_free_space($this->localCacheDir);
        $total = @disk_total_space($this->localCacheDir);
        if ($free === false || $total === false || $total <= 0 || $free >= $total) {
            return 0;
        }

        return (int)(100.0 * ($total - $free) / $total);
    }

    /**
     * Whether the L1 partition has reached the configured cleanup threshold.
     *
     * @return bool
     */
    private function isLocalCacheSpaceExceeded(): bool
    {
        return $this->getLocalFillingPercentage() >= $this->cleanupPercentage;
    }

    /**
     * Throttle the (syscall-bearing) disk-space check so it does not stat the disk on every save.
     * Mirrors the legacy ~1/101 probability. Kept as a protected seam so the eviction path can be
     * exercised deterministically in tests.
     *
     * @return bool
     */
    protected function shouldCheckLocalSpace(): bool
    {
        // mt_rand() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return !mt_rand(0, 100);
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

        // Do NOT route through save(): its isRemoteUpToDate() short-circuit skips the remote write
        // when the data is unchanged, which is exactly the touch() case (same bytes, longer TTL) and
        // would leave the remote lifetime untouched. Re-persist the data and its :hash to the remote
        // directly so the L2 TTL is actually extended, then refresh the local (L1) copy to match.
        try {
            $remoteSaved = $this->remote->save($data, $id, [], $extraLifetime);
            if ($remoteSaved === false) {
                return false;
            }
            $hash = $this->getDataHash($data);
            $this->remote->save($hash, $id . self::HASH_SUFFIX, [], $extraLifetime);
        } catch (\Exception $e) {
            return false;
        }

        $this->local->save($data, $id, [], $extraLifetime);
        $this->markValid($id);

        return true;
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
     * @param bool $knownInvalid Set by callers that already confirmed the marker exists, to skip
     *        the redundant re-check (e.g. handleInvalidKey(), which only runs after load() has
     *        already called isInvalid() and found it true).
     * @return void
     */
    private function markValid(string $id, bool $knownInvalid = false): void
    {
        if ($knownInvalid || $this->isInvalid($id)) {
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
            // Caller (load()) already confirmed isInvalid($id) is true to reach this method.
            $this->markValid($id, true);
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
     * Release the regeneration lock for $id if this process acquired it (ownership-safe, no-op otherwise).
     *
     * Cheap: only issues a call when this process is recorded as the lock holder.
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
     * Generate a unique per-process lock signature (pid-host-random) so lock ownership is unambiguous across servers.
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
