<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

/**
 * Composite stale cache notifier
 *
 * Introduces an extension point to be used by other modules for disabling
 * own cache write when stale cache load detected
 */
class CompositeStaleCacheNotifier implements StaleCacheNotifierInterface
{
    /**
     * @var StaleCacheNotifierInterface[]
     */
    private $notifiers = [];

    /**
     * CompositeStaleCacheNotifier constructor.
     * @param StaleCacheNotifierInterface[] $notifiers
     */
    public function __construct(array $notifiers = [])
    {
        $this->notifiers = $notifiers;
    }

    /**
     * Notifies every added cache notifier of stale cache
     */
    public function cacheLoaderIsUsingStaleCache()
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->cacheLoaderIsUsingStaleCache();
        }
    }
}
