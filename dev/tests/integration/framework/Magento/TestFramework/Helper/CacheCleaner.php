<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Helper;

use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Helper for cleaning cache
 */
class CacheCleaner
{
    /**
     * Clean cache by specified types
     *
     * @param array $cacheTypes
     */
    public static function clean(array $cacheTypes = [])
    {
        $cachePool = self::getCachePool();
        foreach ($cacheTypes as $cacheType) {
            $cachePool->get($cacheType)->clean();
        }
    }

    /**
     * Clean all cache
     */
    public static function cleanAll()
    {
        $cachePool = self::getCachePool();
        foreach ($cachePool as $cacheType) {
            $cacheType->clean();
        }
    }

    /**
     * Get cache pool
     *
     * @return Pool
     */
    private static function getCachePool()
    {
        return Bootstrap::getObjectManager()
            ->get(Pool::class);
    }
}
