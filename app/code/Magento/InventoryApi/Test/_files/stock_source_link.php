<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceRepositoryInterface $sourceRepository */
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var SortOrderBuilder $sortOrderBuilder */
$sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

$sortOrder = $sortOrderBuilder
    ->setField(SourceInterface::NAME)
    ->setDirection(SortOrder::SORT_ASC)
    ->create();
$searchCriteria = $searchCriteriaBuilder
    ->addFilter(SourceInterface::NAME, ['source-name-1', 'source-name-2', 'source-name-3', 'source-name-4'], 'in')
    ->addSortOrder($sortOrder)
    ->create();
/** @var \Magento\InventoryApi\Api\Data\SourceInterface[] $sources */
$sources = array_values($sourceRepository->getList($searchCriteria)->getItems());

$sortOrder = $sortOrderBuilder
    ->setField(StockInterface::NAME)
    ->setDirection(SortOrder::SORT_ASC)
    ->create();
$searchCriteria = $searchCriteriaBuilder
    ->addFilter(StockInterface::NAME, ['stock-name-1', 'stock-name-2'], 'in')
    ->addSortOrder($sortOrder)
    ->create();
/** @var StockInterface[] $stocks */
$stocks = array_values($stockRepository->getList($searchCriteria)->getItems());

/** @var AssignSourcesToStockInterface $assignSourcesToStock */
$assignSourcesToStock = Bootstrap::getObjectManager()->get(AssignSourcesToStockInterface::class);
$assignSourcesToStock->execute([$sources[0]->getSourceId(), $sources[1]->getSourceId()], $stocks[0]->getStockId());
$assignSourcesToStock->execute([$sources[2]->getSourceId(), $sources[3]->getSourceId()], $stocks[1]->getStockId());
