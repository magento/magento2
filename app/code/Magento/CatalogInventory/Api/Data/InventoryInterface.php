<?php

namespace Magento\CatalogInventory\Api\Data;

use Magento\CatalogInventory\Api\ConfigurationAwareInterface;

/**
 * Inventory interface
 *
 * @api
 */
interface InventoryInterface extends InventoryDataInterface, ConfigurationAwareInterface
{
    /**
     * Returns true if this inventory should be indexed
     *
     * @return bool
     */
    public function isIndexed();
}
