<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\Layout;

/**
 * Layout cache key model
 */
class CacheKey implements \Magento\Framework\View\Layout\CacheKeyInterface
{
    /**
     * Cache keys to be able to generate different cache id for same handles
     *
     * @var array
     */
    private $cacheKeys = [];

    /**
     * Add cache key(s) for generating different cache id for same handles
     *
     * @param array|string $cacheKeys
     */
    public function addCacheKey($cacheKeys)
    {
        if (!is_array($cacheKeys)) {
            $cacheKeys = [$cacheKeys];
        }
        $this->cacheKeys = array_merge($this->cacheKeys, $cacheKeys);
    }

    /**
     * Return cache keys array stored
     *
     * @return array
     */
    public function getCacheKeys() {
        return $this->cacheKeys;
    }
}
