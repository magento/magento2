<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\ConfigurationInterface;

/**
 * Inventory record configuration interface
 *
 * Object must be instantiated by inventory record repository
 *
 * Classes of this interface must use inventory configuration as fallback
 *
 * @api
 */
interface InventoryRecordConfigurationInterface extends ConfigurationInterface
{
    /**
     * Returns inventory configuration instance
     *
     * This object is going to be used as a fallback
     *
     * @return ConfigurationInterface
     */
    public function getInventoryConfiguration();
}
