<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\ConfigurationInterface;

/**
 * Inventory configuration interface
 *
 * Object must be instantiated by inventory repository
 *
 * Classes of this interface must use store configuration as fallback
 * for inventory configuration
 *
 * @api
 */
interface InventoryConfigurationInterface extends ConfigurationInterface
{

}
