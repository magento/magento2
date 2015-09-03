<?php

namespace Magento\CatalogInventory\Api\Data;


/**
 * Inventory record interface
 *
 * It is specially designed to be used on a frontend,
 * without possibility to change status or qty of record directly
 *
 * @api
 */
interface InventoryRecordInterface extends InventoryIndexRecordInterface
{
    /**
     * Returns instance of inventory interface
     *
     * Object should be set by inventory record repository
     *
     * @return InventoryInterface
     */
    public function getInventory();
}
