<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\Cache;

use Magento\Framework\Cache\StaleCacheNotifierInterface;

/**
 * Modifier of runtime cache state based on stale data notification from cache loader
 */
class RuntimeStaleCacheStateModifier implements StaleCacheNotifierInterface
{
    /** @var StateInterface */
    private $cacheState;

    /** @var string[] */
    private $cacheTypes;

    /**
     * @param StateInterface $cacheState
     * @param string[] $cacheTypes
     */
    public function __construct(StateInterface $cacheState, array $cacheTypes = [])
    {
        $this->cacheState = $cacheState;
        $this->cacheTypes = $cacheTypes;
    }

    /**
     * Disabled configures cache types when stale cache was detected in the current request
     */
    public function cacheLoaderIsUsingStaleCache()
    {
        foreach ($this->cacheTypes as $type) {
            $this->cacheState->setEnabled($type, false);
        }
    }
}
