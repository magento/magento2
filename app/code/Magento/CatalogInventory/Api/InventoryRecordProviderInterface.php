<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryInterface;
use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Inventory record provider
 *
 * It can use scope configuration to retrieve a default inventory,
 * customer data to retrieve inventory based on customer location, etc
 *
 * @api
 */
interface InventoryRecordProviderInterface
{
    /**
     * Returns array of single inventory records per product id in current scope
     *
     * The structure looks like the following:
     * [$productId => InventoryRecordInterface]
     *
     * @param int[] $productIds
     * @param ScopeConfigInterface $scopeConfig
     * @return InventoryRecordInterface[]
     */
    public function getSingleRecords(array $productIds, ScopeConfigInterface $scopeConfig);

    /**
     * Returns array of multiple inventory records per product in current scope
     *
     * The structure looks like the following:
     * [$productId => [InventoryRecordInterface, InventoryRecordInterface, InventoryRecordInterface]]
     *
     * @param int[] $productIds
     * @param ScopeConfigInterface $scopeConfig
     * @return InventoryRecordInterface[][]
     */
    public function getMultipleRecords(array $productIds, ScopeConfigInterface $scopeConfig);
}
