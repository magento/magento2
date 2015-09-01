<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;

/**
 * Response for request of the inventory records
 *
 * @api
 */
interface InventoryResponseInterface
{
    /**
     * Returns request identifier it will be used to return matched inventory record
     *
     * The best option to use spl_object_hash
     *
     * @return string
     */
    public function getId();

    /**
     * Returns an associative multidimensional array of requested inventory records
     *
     * The result looks like the following:
     * [$productId => [InventoryRecordInterface, InventoryRecordInterface]]
     *
     * @return InventoryRecordInterface[][]
     */
    public function getProductInventoryRecords();
}
