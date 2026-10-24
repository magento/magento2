<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;

/**
 * Low-level backend wrapper for Symfony cache adapter
 *
 * Provides backend-level methods for tag operations and cache cleaning
 * Used by tests and utilities that need direct backend access
 */
class LowLevelBackend
{
    /**
     * @var TagAdapterInterface
     */
    private TagAdapterInterface $adapter;

    /**
     * @param TagAdapterInterface $adapter
     */
    public function __construct(TagAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
            return $this->adapter->getIdsMatchingTags($tags);
        }
        return [];
    }

    /**
     * Clean cache entries
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = []): bool
    {
        // Backend clean is handled by adapter
        if ($mode === CacheConstants::CLEANING_MODE_ALL) {
            if (method_exists($this->adapter, 'clearAllIndices')) {
                $this->adapter->clearAllIndices();
            }
        }
        return true;
    }
}
