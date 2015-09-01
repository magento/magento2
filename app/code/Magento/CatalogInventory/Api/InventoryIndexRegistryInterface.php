<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;
use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;

/**
 * Inventory index registry interface
 *
 * Place to retrieve index records and find appropriate indexer for inventory
 *
 * @api
 */
interface InventoryIndexRegistryInterface
{
    /**
     * @param InventoryInterface $inventory
     * @return mixed
     */
    public function getIndexerByInventory(InventoryInterface $inventory);

    /**
     * Returns list of inventory index records by product identifiers
     *
     * @param InventoryInterface $inventory
     * @param int[] $productIds
     * @return InventoryIndexRecordInterface[]
     */
    public function getIndexRecords(InventoryInterface $inventory, array $productIds);
}
