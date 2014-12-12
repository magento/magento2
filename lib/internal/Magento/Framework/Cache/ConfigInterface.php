<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
