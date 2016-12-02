<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Handle cache for tests executions.
 */
class Cache extends Cli
{
    /**
     * Parameter for flush cache command.
     */
    const PARAM_CACHE_FLUSH = 'cache:flush';

    /**
     * Parameter for cache disable command.
     */
    const PARAM_CACHE_DISABLE = 'cache:disable';

    /**
     * Parameter for cache enable command.
     */
    const PARAM_CACHE_ENABLE = 'cache:enable';

    /**
     * Flush Cache.
     * If no parameters are set, all cache types are flushed.
     *
     * @param array $cacheTypes
     * @return void
     */
    public function flush(array $cacheTypes = [])
    {
        $options = empty($cacheTypes) ? '' : ' ' . implode(' ', $cacheTypes);
        parent::execute(Cache::PARAM_CACHE_FLUSH . $options);
    }

    /**
     * Disable all cache or one cache type.
     *
     * @param string $cacheType [optional]
     * @return void
     */
    public function disableCache($cacheType = null)
    {
        parent::execute(Cache::PARAM_CACHE_DISABLE . ($cacheType ? " $cacheType" : ''));
    }

    /**
     * Enable all cache or one cache type.
     *
     * @param string $cacheType [optional]
     * @return void
     */
    public function enableCache($cacheType = null)
    {
        parent::execute(Cache::PARAM_CACHE_ENABLE . ($cacheType ? " $cacheType" : ''));
    }
}
