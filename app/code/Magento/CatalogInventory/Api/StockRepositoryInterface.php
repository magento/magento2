<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockRepositoryInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.4/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.4/inventory/inventory-api-reference.html
 */
interface StockRepositoryInterface
{
    /**
     * Save Stock data
     *
     * @param \Magento\CatalogInventory\Api\Data\StockInterface $stock
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockInterface $stock);

    /**
     * Load Stock data by given stockId and parameters
     *
     * @param int $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function get($stockId);

    /**
     * Load Stock data collection by given search criteria
     *
     * @param \Magento\CatalogInventory\Api\StockCriteriaInterface $collectionBuilder
     * @return \Magento\CatalogInventory\Api\Data\StockCollectionInterface
     */
    public function getList(StockCriteriaInterface $collectionBuilder);

    /**
     * Delete stock by given stockId
     *
     * @param \Magento\CatalogInventory\Api\Data\StockInterface $stock
     * @return bool
     */
    public function delete(\Magento\CatalogInventory\Api\Data\StockInterface $stock);
}
