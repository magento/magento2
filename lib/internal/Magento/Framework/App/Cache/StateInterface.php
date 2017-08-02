<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

/**
 * @api
 * @since 2.0.0
 */
interface StateInterface
{
    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @param string $cacheType
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled($cacheType);

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     * @since 2.0.0
     */
    public function setEnabled($cacheType, $isEnabled);

    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     * @since 2.0.0
     */
    public function persist();
}
