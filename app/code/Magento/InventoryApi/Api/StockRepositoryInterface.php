<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * @api
 */
interface StockRepositoryInterface
{
    /**
     * Save Stock data. If you want to create plugin on get method, also you need to create separate plugin
     * on getList method, because entity loading way is different for these methods
     *
     * @param \Magento\InventoryApi\Api\Data\StockInterface $stock
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\InventoryApi\Api\Data\StockInterface $stock);

    /**
     * Get Stock data by given stockId.
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($stockId);

    /**
     * Delte the Stock data by given stockId.
     *
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException | \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($stockId);

    /**
     * Load Stock data collection by given search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\StockSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    );
}
