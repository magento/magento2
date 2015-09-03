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

    /**
     * Returns true if this inventory is virtual
     *
     * Used to restrict purchase of items from it,
     * as it is might be just an aggregated inventory
     *
     * @return bool
     */
    public function isVirtual();
}
