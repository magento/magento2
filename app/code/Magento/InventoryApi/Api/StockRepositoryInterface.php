<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * This is Facade for basic operations with Stock
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockRepositoryInterface
{
    /**
     * Save Stock data
     *
     * @param \Magento\InventoryApi\Api\Data\StockInterface $stock
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(StockInterface $stock);

    /**
     * Get Stock data by given stockId. If you want to create plugin on get method, also you need to create separate
     * plugin on getList method, because entity loading way is different for these methods
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($stockId);

    /**
     * Load Stock data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\StockSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);

    /**
     * Delete the Stock data by given stockId
     *
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($stockId);
}
