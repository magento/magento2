<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Layout;

/**
 * Interface LayoutCacheKeyInterface
 *
 * @api
 */
interface LayoutCacheKeyInterface
{
    /**
     * Add cache key(s) for generating different cache id for same handles
     *
     * @param array|string $cacheKeys
     * @return void
     */
    public function addCacheKeys($cacheKeys);

    /**
     * Return cache keys array
     *
     * @return array
     */
    public function getCacheKeys();
}
