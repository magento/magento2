<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use InvalidArgumentException;
use Magento\Framework\Cache\Backend\BackendInterface;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\PruneableInterface;

/**
 * Backend wrapper for Symfony cache adapter
 *
 * Provides BackendInterface-compatible wrapper for Symfony PSR-6 cache.
 * Delegates operations to the Symfony frontend for proper tag and metadata handling.
 */
class BackendWrapper implements BackendInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var TagAdapterInterface
     */
    private TagAdapterInterface $adapter;

    /**
     * @var FrontendInterface
     */
    private FrontendInterface $symfony;

    /**
     * @param CacheItemPoolInterface $cache
     * @param TagAdapterInterface $adapter
     * @param FrontendInterface $symfony
     */
    public function __construct(
        CacheItemPoolInterface $cache,
        TagAdapterInterface $adapter,
        FrontendInterface $symfony
    ) {
        $this->cache = $cache;
        $this->adapter = $adapter;
        $this->symfony = $symfony;
    }

    /**
     * Test if a cache is available for the given id
     *
     * @param string $id Cache id
     * @return int|false Last modified timestamp if available, false otherwise
     */
    public function test($id)
    {
        return $this->symfony->test($id);
    }

    /**
     * Load value with given id from cache
     *
     * @param string $id Cache id
     * @param bool $doNotTestCacheValidity If true, validity not tested
     * @return string|false Cached data or false if not available
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        // Delegate to frontend (validity always tested in Symfony)
        return $this->symfony->load($id);
    }

    /**
     * Save some data in cache
     *
     * @param mixed $data Data to cache
     * @param string $id Cache id
     * @param array $tags Array of tags
     * @param int|null $specificLifetime Specific lifetime (null = infinite)
     * @return bool True if no problem
     */
    public function save($data, $id, $tags = [], $specificLifetime = null)
    {
        // Delegate to frontend for full save logic
        return $this->symfony->save($data, $id, $tags, $specificLifetime);
    }

    /**
     * Remove a cache record
     *
     * @param string $id Cache id
     * @return bool True if no problem
     */
    public function remove($id)
    {
        // Delegate to frontend
        return $this->symfony->remove($id);
    }

    /**
     * Clean some cache records
     *
     * @param string $mode Clean mode ('all', 'old')
     * @param array $tags Array of tags (unused for backend clean)
     * @return bool True if no problem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean($mode = 'all', $tags = [])
    {
        return match ($mode) {
            CacheConstants::CLEANING_MODE_ALL, 'all' => $this->clear(),
            CacheConstants::CLEANING_MODE_OLD, 'old' => $this->cleanOld(),
            default => throw new InvalidArgumentException("Backend clean only supports ALL and OLD modes")
        };
    }

    /**
     * Garbage-collect expired items and orphaned tag-index members, similar to legacy clean(OLD) behavior.
     *
     * @return bool
     */
    private function cleanOld(): bool
    {
        // Remove expired entries from the underlying store. FilesystemAdapter physically deletes
        // expired files here (parity with the legacy file backend); Redis auto-expires so this no-ops.
        $this->prune();
        // Sweep orphaned tag-index members (ids whose data key already expired). No-op on file/generic.
        return $this->adapter->garbageCollect() >= 0;
    }

    /**
     * Prune expired entries from the underlying pool when it supports pruning (e.g. FilesystemAdapter).
     *
     * @return bool
     */
    public function prune(): bool
    {
        return $this->cache instanceof PruneableInterface ? $this->cache->prune() : false;
    }

    /**
     * Clear all cache entries
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->adapter->clearAllIndices();
        return $this->cache->clear();
    }

    /**
     * @inheritdoc
     */
    public function setDirectives($directives)
    {
        // Intentional no-op: Symfony backend options are not stored in the wrapper
    }
}
