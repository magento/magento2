<?php

namespace Magento\CatalogInventory\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Response for request of the inventory records
 *
 * @api
 */
interface InventoryRecordCollectionInterface extends SearchResultsInterface
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
     * Get items
     *
     * @return \Magento\CatalogInventory\Api\Data\InventoryRecordInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Magento\CatalogInventory\Api\Data\InventoryRecordInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Returns list of inventory items,
     * grouped by product identifier as specified in search criteria
     *
     * @return \Magento\CatalogInventory\Api\Data\InventoryRecordInterface[][]
     */
    public function getItemsByProductQuantityCriteria();

    /**
     * Get search criteria.
     *
     * @return \Magento\CatalogInventory\Api\InventoryRecordCriteriaInterface
     */
    public function getSearchCriteria();
}
