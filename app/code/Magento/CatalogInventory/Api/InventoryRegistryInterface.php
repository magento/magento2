<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;
use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;

interface InventoryRegistryInterface
{
    /**
     * Returns main store inventory based on store configuration
     * or other environment dependencies
     *
     * This one should be used only for filtering stock status on collections
     *
     * @param int|null $storeId
     * @return InventoryInterface
     */
    public function getMainInventory($storeId = null);

    /**
     * Returns array of single inventory records per product id in current scope
     *
     * The structure looks like the following:
     * [$productId => InventoryRecordInterface]
     *
     * @param int[] $productIds
     * @param int|null $storeId
     * @return InventoryRecordInterface[]
     */
    public function getSingleInventoryRecordForProducts(array $productIds, $storeId = null);

    /**
     * Returns array of multiple inventory records per product in current scope
     *
     * The structure looks like the following:
     * [$productId => [InventoryRecordInterface, InventoryRecordInterface, InventoryRecordInterface]]
     *
     * @param int[] $productIds
     * @param int|null $storeId
     * @return InventoryRecordInterface[][]
     */
    public function getMultipleInventoryRecordsForProducts(array $productIds, $storeId = null);

    /**
     * Returns inventory records for specific delivery location and by taking into account
     * available quantities in different warehouses
     *
     * @param InventoryRequestInterface[] $requests
     * @param LocationInformationInterface $location
     * @param int|null $storeId
     * @return InventoryRecordInterface[][]
     */
    public function getInventoryRecordsForLocationByRequests(
        array $requests, LocationInformationInterface $location, $storeId = null
    );
}
