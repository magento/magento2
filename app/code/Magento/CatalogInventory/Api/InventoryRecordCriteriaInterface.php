<?php

namespace Magento\CatalogInventory\Api;

/**
 * Request for the inventory records
 *
 * Extended from stock item criteria interface to make it backward compatible
 *
 * @api
 */
interface InventoryRecordCriteriaInterface extends StockItemCriteriaInterface
{
    /**
     * Sets product quantities criteria
     *
     * @param array $productQuantities
     * @return $this
     */
    public function setProductQuantities(array $productQuantities);

    /**
     * Returns an associative array of product requested quantities
     * in relation to its identifiers
     *
     * The result looks like the following:
     * [$productId => $productQty]
     *
     * @return string[]
     */
    public function getProductQuantities();

    /**
     * Flag for notifying inventory resolver that multiple inventories
     * are allowed for response
     *
     * @param bool $flag
     * @return $this
     */
    public function setAllowMultipleRecordsPerProduct($flag);

    /**
     * Sets multiple records per product
     *
     * @return bool
     */
    public function isMultipleRecordsPerProduct();

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
