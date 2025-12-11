<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

/**
 * Notifier for stale cache retrieval detection
 *
 * @api
 */
interface StaleCacheNotifierInterface
{
    /**
     * Notifies of stale cache being used by any cache loader
     */
    public function cacheLoaderIsUsingStaleCache();
}
