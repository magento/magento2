<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;

/**
 * Inventory indexer interface
 *
 * @api
 */
interface InventoryIndexerInterface
{
    /**
     * Reindex data for inventory
     *
     * When second parameter is specified, it limits indexation to specified products only
     *
     * @param InventoryInterface $inventory
     * @param array|null $productIds
     * @return $this
     */
    public function reindex(InventoryInterface $inventory, array $productIds = null);

    /**
     * Reindex inventory directly by supplying changed inventory records
     *
     * @param int[] $inventoryRecordIds
     * @return $this
     */
    public function reindexRecords(array $inventoryRecordIds);
}
