<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Exception\CacheException;
use Magento\Framework\Cache\FrontendInterface;

/**
 * L2 (Two-Level) Cache Backend for Symfony Adapters
 *
 * This backend provides local + remote caching with automatic synchronization,
 * designed specifically for Symfony cache adapters (PSR-6 compliant).
 *
 * Unlike RemoteSynchronizedCache (which requires ExtendedBackendInterface),
 * this class works directly with Symfony's FrontendInterface.
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
     * @param string $id
     * @return void
     */
    private function markValid(string $id): void
    {
        $this->local->remove(self::INVALID_KEY_PREFIX . $id);
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
            return $localData;
        }

        $localHash = $this->getDataHash($localData);

        if ($remoteHash === $localHash) {
            return $localData;
        }

        return null;
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
