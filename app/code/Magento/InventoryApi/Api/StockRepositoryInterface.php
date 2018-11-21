<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * In Magento 2 Repository considered as an implementation of Facade pattern which provides a simplified interface
 * to a larger body of code responsible for Domain Entity management
 *
 * The main intention is to make API more readable and reduce dependencies of business logic code on the inner workings
 * of a module, since most code uses the facade, thus allowing more flexibility in developing the system
 *
 * Along with this such approach helps to segregate two responsibilities:
 * 1. Repository now could be considered as an API - Interface for usage (calling) in the business logic
 * 2. Separate class-commands to which Repository proxies initial call (like, Get Save GetList Delete) could be
 *    considered as SPI - Interfaces that you should extend and implement to customize current behaviour
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
     * @throws \Magento\Framework\Validation\ValidationException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\InventoryApi\Api\Data\StockInterface $stock): int;

    /**
     * Get Stock data by given stockId. If you want to create plugin on get method, also you need to create separate
     * plugin on getList method, because entity loading way is different for these methods
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $stockId): \Magento\InventoryApi\Api\Data\StockInterface;

    /**
     * Find Stocks by given SearchCriteria
     * SearchCriteria is not required because load all stocks is useful case
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\StockSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ): \Magento\InventoryApi\Api\Data\StockSearchResultsInterface;

    /**
     * Delete the Stock data by stockId. If stock is not found do nothing
     *
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $stockId): void;
}
