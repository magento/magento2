<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusRepositoryInterface
 * @api
 */
interface StockStatusRepositoryInterface
{
    /**
     * Save StockStatus data
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);

    /**
     * Load StockStatus data by given stockStatusId and parameters
     *
     * @param string $stockStatusId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function get($stockStatusId);

    /**
     * Load Stock Status data collection by given search criteria
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $searchCriteria
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getList(StockStatusCriteriaInterface $searchCriteria);

    /**
     * Delete StockStatus entity
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return bool
     */
    public function delete(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);
}
