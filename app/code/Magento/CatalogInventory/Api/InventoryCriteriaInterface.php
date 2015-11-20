<?php

namespace Magento\CatalogInventory\Api;

/**
 * Request for the inventory records
 *
 * Extended from stock item criteria interface to make it backward compatible
 *
 * @api
 */
interface InventoryCriteriaInterface extends StockCriteriaInterface
{
    /**
     * Sets inventory identifier filters for criteria
     *
     * @param int[] $inventoryIds
     * @return $this
     */
    public function setInventoryFilter($inventoryIds);

    /**
     * Sets location filter, to retrieve inventory records based on location
     *
     * @param LocationInformationInterface $location
     * @return $this
     */
    public function setLocationFilter(LocationInformationInterface $location);
}
