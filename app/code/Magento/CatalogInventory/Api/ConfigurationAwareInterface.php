<?php

namespace Magento\CatalogInventory\Api;

/**
 * Interface for objects that are aware of inventory configuration,
 * and might have own version of it
 *
 * It is recommended to set configuration via constructor arguments
 *
 */
interface ConfigurationAwareInterface
{
    /**
     * Returns configuration object that is going to be used as configuration options
     *
     * Object should be set by entity repository
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();
}
