<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;

/**
 * Inventory registry interface
 *
 * The main point, where inventory information is retrieved for Magento
 *
 * @api
 */
interface InventoryManagerInterface
{
    /**
     * Returns main store inventory based on scope configuration
     * or other environment dependencies
     *
     * @param InventoryCriteriaInterface $criteria
     * @return InventoryInterface
     */
    public function getInventory(InventoryCriteriaInterface $criteria);

    /**
     * Returns index criteria,
     * that will be used for filtering product collection on stock availability
     *
     * @param InventoryCriteriaInterface $criteria
     * @return InventoryIndexRecordCriteriaInterface
     */
    public function getIndexCriteria(InventoryCriteriaInterface $criteria);

    /**
     * Returns collection of inventory records per criteria request
     *
     * @param InventoryRecordCriteriaInterface[] $requests
     * @return InventoryRecordCollectionInterface[]
     */
    public function getInventoryRecords($requests);
}
