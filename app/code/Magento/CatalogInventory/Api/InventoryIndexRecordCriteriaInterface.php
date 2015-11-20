<?php

namespace Magento\CatalogInventory\Api;

/**
 * Request for the inventory index records
 *
 * @api
 */
interface InventoryIndexRecordCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\InventoryIndexRecordCriteriaInterface $criteria
     * @return bool
     */
    public function addCriteria(\Magento\CatalogInventory\Api\InventoryIndexRecordCriteriaInterface $criteria);

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

    /**
     * Add scope filter to collection
     *
     * @param int $scope
     * @return bool
     */
    public function setScopeFilter($scope);
}
