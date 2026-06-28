<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Low-level frontend wrapper for Symfony cache adapter
 *
 * Provides backward-compatible interface for legacy code
 * Used by code that needs direct access to cache internals
 */
class LowLevelFrontend
{
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var FrontendInterface
     */
    private FrontendInterface $symfony;

    /**
     * @var TagAdapterInterface
     */
    private TagAdapterInterface $adapter;

    /**
     * @var string
     */
    private string $idPrefix;

    /**
     * @var int
     */
    private int $lifetime;

    /**
     * @var LowLevelBackend|null
     */
    private ?LowLevelBackend $backend = null;

    /**
     * @param CacheItemPoolInterface $cache
     * @param FrontendInterface $symfony
     * @param TagAdapterInterface $adapter
     * @param string $idPrefix
     * @param int $lifetime
     */
    public function __construct(
        CacheItemPoolInterface $cache,
        FrontendInterface $symfony,
        TagAdapterInterface $adapter,
        string $idPrefix,
        int $lifetime = 7200
    ) {
        $this->cache = $cache;
        $this->symfony = $symfony;
        $this->adapter = $adapter;
        $this->idPrefix = $idPrefix;
        $this->lifetime = $lifetime;
    }

    /**
     * Get metadata for cache entry
     *
     * @param string $id
     * @return array|false
     */
    public function getMetadatas($id)
    {
        return $this->symfony->getMetadatas($id);
    }

    /**
     * Get cache option
     *
     * @param string $name
     * @return mixed
     */
    public function getOption(string $name)
    {
        if ($name === 'cache_id_prefix') {
            return $this->idPrefix;
        }
        if ($name === 'lifetime') {
            return $this->lifetime;
        }
        return null;
    }

    /**
     * Get IDs matching tags
     *
     * @param array $tags
     * @return array
     */
    public function getIdsMatchingTags(array $tags): array
    {
        // Get IDs from helper (uses backend-specific logic)
        if (method_exists($this->adapter, 'getIdsMatchingTags')) {
            // Tags are already in the correct format from the caller
            // Helper will add namespace prefix internally
            return $this->adapter->getIdsMatchingTags($tags);
        }

        // For GenericAdapterHelper, return empty array
        // (it doesn't support native ID lookup by tags)
        return [];
    }

    /**
     * Get backend wrapper
     *
     * @return LowLevelBackend
     */
    public function getBackend(): LowLevelBackend
    {
        if ($this->backend === null) {
            $this->backend = new LowLevelBackend($this->adapter);
        }
        return $this->backend;
    }

    /**
     * Clean cache entries
     *
     * Delegates to Symfony frontend adapter to ensure proper Lua integration
     *
     * @param string $mode Cleaning mode
     * @param array $tags Tags array
     * @return bool
     */
    public function clean($mode = 'all', array $tags = []): bool
    {
        // Delegate to Symfony frontend for proper Lua script integration
        return $this->symfony->clean($mode, $tags);
    }

    /**
     * Delegate all other method calls to the cache
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->cache->$method(...$arguments);
    }
}
