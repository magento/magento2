<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockRegistryInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory (MSI) because legacy APIs cannot represent salable
 *    quantity per source.
 * @see \Magento\InventorySalesApi\Api\StockResolverInterface
 * @see https://developer.adobe.com/commerce/webapi/rest/inventory/index.html
 * @see https://developer.adobe.com/commerce/webapi/rest/inventory/inventory-api-reference.html
 */
interface StockRegistryInterface
{
    /**
     * Returns stock metadata for the given scope.
     *
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId = null);

    /**
     * Returns the stock item for a product ID.
     *
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId = null);

    /**
     * Returns the stock item for a product SKU.
     *
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $scopeId = null);

    /**
     * Returns the stock status for a product ID.
     *
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId = null);

    /**
     * Returns the stock status for a product SKU.
     *
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $scopeId = null);

    /**
     * Retrieve Product stock status
     *
     * @param int $productId
     * @param int $scopeId
     * @return int
     */
    public function getProductStockStatus($productId, $scopeId = null);

    /**
     * Returns the numeric stock status for a product SKU.
     *
     * @param string $productSku
     * @param int $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $scopeId = null);

    /**
     * Retrieves a list of SKU's with low inventory qty
     *
     * @param int $scopeId
     * @param float $qty
     * @param int $currentPage
     * @param int $pageSize
     * @return \Magento\CatalogInventory\Api\Data\StockItemCollectionInterface
     */
    public function getLowStockItems($scopeId, $qty, $currentPage = 1, $pageSize = 0);

    /**
     * Updates the stock item for a product SKU.
     *
     * @param string $productSku
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);
}
