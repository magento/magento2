<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            $cachePool->get($cacheType)->getBackend()->clean();
        }
    }

    /**
     * Clean all cache
     */
    public static function cleanAll()
    {
        $cachePool = self::getCachePool();
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
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
