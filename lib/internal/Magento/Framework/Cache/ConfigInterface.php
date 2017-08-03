<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

/**
 * Interface \Magento\Framework\Cache\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get configuration of all cache types
     *
     * @return array
     * @since 2.0.0
     */
    public function getTypes();

    /**
     * Get configuration of specified cache type
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getType($type);
}
