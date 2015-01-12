<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockRegistryInterface
 */
interface StockRegistryInterface
{
    /**
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($websiteId = null);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $websiteId = null);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $websiteId = null);

    /**
     * Retrieve Product stock status
     *
     * @param int $productId
     * @param int $websiteId
     * @return int
     */
    public function getProductStockStatus($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return int
     * @throw \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $websiteId = null);

    /**
     * Retrieves a list of SKU's with low inventory qty
     *
     * @param int $websiteId
     * @param float $qty
     * @param int $currentPage
     * @param int $pageSize
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getLowStockItems($websiteId, $qty, $currentPage = 1, $pageSize = 0);

    /**
     * @param string $productSku
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);
}
