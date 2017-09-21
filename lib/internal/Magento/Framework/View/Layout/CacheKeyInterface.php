<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Interface CacheKeyInterface
 */
interface CacheKeyInterface
{
    /**
     * Add cache key for generating different cache id for same handles
     *
     * @param array|string $cacheKey
     */
    public function addCacheKey($cacheKey);

    /**
     * Return cache keys array stored
     *
     * @return array
     */
    public function getCacheKeys();
}
