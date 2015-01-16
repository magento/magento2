<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

interface ConfigInterface
{
    /**
     * Get configuration of all cache types
     *
     * @return array
     */
    public function getTypes();

    /**
     * Get configuration of specified cache type
     *
     * @param string $type
     * @return array
     */
    public function getType($type);
}
