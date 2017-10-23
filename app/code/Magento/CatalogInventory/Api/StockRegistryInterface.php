<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockRegistryInterface
 * @api
 * @since 100.0.2
 */
interface StockRegistryInterface
{
    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId = null);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId = null);

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $scopeId = null);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId = null);

    /**
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
     * @param string $productSku
     * @param int $scopeId
     * @return int
     * @throw \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $scopeId = null);

    /**
     * Retrieves a list of SKU's with low inventory qty
     *
     * @param int $scopeId
     * @param float $qty
     * @param int $currentPage
     * @param int $pageSize
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getLowStockItems($scopeId, $qty, $currentPage = 1, $pageSize = 0);

    /**
     * @param string $productSku
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);
}
