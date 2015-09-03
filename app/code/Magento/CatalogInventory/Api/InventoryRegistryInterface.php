<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;
use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;
use Magento\Framework\App\ScopeInterface;

/**
 * Inventory registry interface
 *
 * The main point, where inventory information is retrieved for Magento
 *
 * @api
 */
interface InventoryRegistryInterface
{
    /**
     * Returns main store inventory based on scope configuration
     * or other environment dependencies
     *
     * This one should be used only for filtering stock status on collections,
     * also should be used in indexers for applying inventory status limitations
     *
     * @param ScopeInterface $scope
     * @return InventoryInterface
     */
    public function getInventory(ScopeInterface $scope);

    /**
     * Returns array of single inventory records per product id in current scope
     *
     * The structure looks like the following:
     * [$productId => InventoryRecordInterface]
     *
     * @param int[] $productIds
     * @param ScopeInterface $scope
     * @return InventoryRecordInterface[]
     */
    public function getSingleRecordForProducts(array $productIds, ScopeInterface $scope);

    /**
     * Returns array of multiple inventory records per product in current scope
     *
     * The structure looks like the following:
     * [$productId => [InventoryRecordInterface, InventoryRecordInterface, InventoryRecordInterface]]
     *
     * @param int[] $productIds
     * @param ScopeInterface $scope
     * @return InventoryRecordInterface[][]
     */
    public function getMultipleRecordsForProducts(array $productIds, ScopeInterface $scope);

    /**
     * Returns inventory records for specific delivery location and by taking into account
     * available quantities in different warehouses
     *
     * The structure looks like the following:
     * [$request->getId() => InventoryResponseInterface]
     *
     * In case if some of the requests cannot be full filled, the result will simply miss the item in array.
     * Also implementation should not return non completed request, as request is an integral
     *
     * It is up to an invoker how to process missing request result.
     *
     * @param InventoryRequestInterface[] $requests
     * @param LocationInformationInterface $location
     * @param ScopeInterface $scope
     * @return InventoryResponseInterface[]
     */
    public function getRecordsForLocationByRequests(
        array $requests, LocationInformationInterface $location, ScopeInterface $scope
    );
}
