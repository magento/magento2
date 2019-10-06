<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

/**
 * Notifier for stale cache retrieval detection
 */
interface StaleCacheNotifierInterface
{
    /**
     * Notifies of stale cache being used by any cache loader
     */
    public function cacheLoaderIsUsingStaleCache();
}
