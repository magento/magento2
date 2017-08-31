<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder
    ->addFilter(StockInterface::NAME, ['stock-name-1', 'stock-name-1-updated'], 'in')
    ->create();
$searchResult = $stockRepository->getList($searchCriteria);

if ($searchResult->getTotalCount()) {
    $items = $searchResult->getItems();
    $stock = reset($items);
    $stockRepository->deleteById($stock->getStockId());
}
