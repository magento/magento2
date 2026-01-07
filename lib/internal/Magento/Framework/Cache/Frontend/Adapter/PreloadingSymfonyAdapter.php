<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Preloading wrapper for Symfony cache adapter
 *
 * Preloads frequently accessed cache keys into local PHP memory on initialization
 * to eliminate Redis network roundtrips for critical configuration data.
 */
class PreloadingSymfonyAdapter implements FrontendInterface
{
    /**
     * @var FrontendInterface
     */
    private FrontendInterface $adapter;

    /**
     * @var array
     */
    private array $localCache = [];

    /**
     * @var array
     */
    private array $preloadKeys;

    /**
     * Constructor
     *
     * @param FrontendInterface $adapter Underlying cache adapter
     * @param array $preloadKeys List of cache key identifiers to preload
     */
    public function __construct(
        FrontendInterface $adapter,
        array $preloadKeys = []
    ) {
        $this->adapter = $adapter;
        $this->preloadKeys = $preloadKeys;

        // Preload keys on initialization (one-time cost per worker)
        if (!empty($preloadKeys)) {
            $this->preloadKeys($preloadKeys);
        }
    }

    /**
     * Preload specified keys from Redis into local memory
     *
     * @param array $keys
     * @return void
     */
    private function preloadKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $value = $this->adapter->load($key);
            if ($value !== false) {
                $this->localCache[$key] = $value;
            }
        }
    }

    /**
     * @inheritDoc
     *
     * Checks local cache first before delegating to Redis
     */
    public function load($identifier)
    {
        // Fast path: check local cache first (0.0001ms)
        if (isset($this->localCache[$identifier])) {
            return $this->localCache[$identifier];
        }

        // Slow path: fetch from Redis (0.15ms)
        return $this->adapter->load($identifier);
    }

    /**
     * @inheritDoc
     *
     * Writes through to Redis (bypasses local cache to avoid stale data)
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        // Write through to Redis
        $result = $this->adapter->save($data, $identifier, $tags, $lifeTime);

        // If this is a preloaded key, update local cache
        if ($result && in_array($identifier, $this->preloadKeys, true)) {
            $this->localCache[$identifier] = $data;
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter
     */
    public function test($identifier)
    {
        return $this->adapter->test($identifier);
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears from local cache
     */
    public function remove($identifier)
    {
        // Remove from local cache if present
        unset($this->localCache[$identifier]);

        return $this->adapter->remove($identifier);
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears local cache
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        // Clear local cache on clean operations
        $this->localCache = [];

        // Re-preload after clean if needed
        $result = $this->adapter->clean($mode, $tags);

        if ($result && !empty($this->preloadKeys)) {
            $this->preloadKeys($this->preloadKeys);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getBackend()
    {
        return $this->adapter->getBackend();
    }

    /**
     * @inheritDoc
     */
    public function getLowLevelFrontend()
    {
        return $this->adapter->getLowLevelFrontend();
    }

    /**
     * Get statistics about preloaded keys
     *
     * Useful for monitoring and debugging
     *
     * @return array
     */
    public function getPreloadStats(): array
    {
        return [
            'preload_keys_configured' => count($this->preloadKeys),
            'preload_keys_cached' => count($this->localCache),
            'cached_keys' => array_keys($this->localCache),
        ];
    }
}
