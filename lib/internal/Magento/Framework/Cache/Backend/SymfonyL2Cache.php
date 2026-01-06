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

        if ($localData !== false) {
            // Check if local data is still valid by comparing hash
            $remoteHash = $this->remote->load($id . self::HASH_SUFFIX);
            $localHash = $this->getDataHash($localData);

            if ($remoteHash === $localHash) {
                // Local cache is up-to-date
                return $localData;
            }

            // Local cache is stale, fall through to load from remote
        }

        // Load from remote cache
        $remoteData = $this->remote->load($id);

        if ($remoteData !== false) {
            // Save to local cache for next time
            $this->local->save($remoteData, $id);
            return $remoteData;
        } elseif ($localData && $this->useStaleCache) {
            // Remote failed but local (stale) data exists, return stale data for high availability
            return $localData;
        }

        return false;
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
        // Calculate and save hash to remote for synchronization
        $hash = $this->getDataHash($data);
        $this->remote->save($hash, $id . self::HASH_SUFFIX, $tags, $specificLifetime);

        // Save data to remote
        $remoteSaved = $this->remote->save($data, $id, $tags, $specificLifetime);

        // Save to local cache
        $this->local->save($data, $id, $tags, $specificLifetime);

        return $remoteSaved;
    }

    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        // Remove hash from remote
        $this->remote->remove($id . self::HASH_SUFFIX);

        // Remove from remote
        $result = $this->remote->remove($id);

        // Only remove from local if NOT using stale cache (keep stale data for availability)
        if (!$this->useStaleCache) {
            $this->local->remove($id);
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
}
