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
     * Whether the one-time preload has run yet (lazy, on first load).
     *
     * @var bool
     */
    private bool $preloaded = false;

    /**
     * Constructor
     *
     * @param FrontendInterface $adapter Underlying cache adapter
     * @param array $preloadKeys List of cache key identifiers to preload
     * @param string $idPrefix Backend id_prefix, so pre-prefixed config keys (e.g. "069_EAV_ENTITY_TYPES")
     *        are normalized to the raw id the application actually loads ("EAV_ENTITY_TYPES")
     */
    public function __construct(
        FrontendInterface $adapter,
        array $preloadKeys = [],
        string $idPrefix = ''
    ) {
        $this->adapter = $adapter;
        // Normalize keys so both raw ("EAV_ENTITY_TYPES") and pre-prefixed ("069_EAV_ENTITY_TYPES")
        // config values resolve to the id the app passes to load(). The adapter re-adds the prefix
        // internally, so a pre-prefixed key would otherwise be double-prefixed and never hit.
        $this->preloadKeys = ($idPrefix !== '')
            ? array_map(
                static fn(string $key): string => str_starts_with($key, $idPrefix)
                    ? substr($key, strlen($idPrefix))
                    : $key,
                $preloadKeys
            )
            : $preloadKeys;
    }

    /**
     * Preload all configured keys in a SINGLE batched round-trip, lazily on first use.
     *
     * Matches the legacy Redis wrapper (Magento\Framework\Cache\Backend\Redis::load): fetch every hot
     * key at once (one pipeline / getItems) rather than N sequential loads, and serve them from local
     * memory afterwards. Falls back to per-key loads only if the underlying adapter cannot batch.
     *
     * NOTE: preload_keys must be the cache ids exactly as the application passes them to load() (the
     * backend id_prefix is applied internally) — do NOT pre-prefix them, or lookups will miss.
     *
     * @return void
     */
    private function ensurePreloaded(): void
    {
        if ($this->preloaded) {
            return;
        }
        $this->preloaded = true;

        if (empty($this->preloadKeys)) {
            return;
        }

        if (method_exists($this->adapter, 'loadMultiple')) {
            // one batched round-trip for all keys
            $this->localCache = $this->adapter->loadMultiple($this->preloadKeys);
            return;
        }

        // fallback: per-key (no batching available on the underlying adapter)
        foreach ($this->preloadKeys as $key) {
            $value = $this->adapter->load($key);
            if ($value !== false) {
                $this->localCache[$key] = $value;
            }
        }
    }

    /**
     * @inheritDoc
     *
     * Checks the preload cache first (populated in one batched round-trip) before delegating to Redis.
     */
    public function load($identifier)
    {
        $this->ensurePreloaded();

        // Fast path: served from the in-process preload cache (no Redis round-trip)
        if (isset($this->localCache[$identifier])) {
            return $this->localCache[$identifier];
        }

        // Slow path: fetch from Redis
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
        // Drop the preload cache and arm a lazy re-preload on the next load().
        $this->localCache = [];
        $this->preloaded = false;

        return $this->adapter->clean($mode, $tags);
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
