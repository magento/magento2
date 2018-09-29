<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceItemRepositoryInterface $sourceItemRepository */
$sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
/** @var SourceItemsDeleteInterface $sourceItemsDelete */
$sourceItemsDelete = Bootstrap::getObjectManager()->get(SourceItemsDeleteInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);

$searchCriteria = $searchCriteriaBuilder->addFilter(
    SourceItemInterface::SKU,
    ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4'],
    'in'
)->create();
$sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();

/**
 * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
 * In that case there is "if" which checks that SKU1, SKU2 and SKU3 still exists in database.
 */
if (!empty($sourceItems)) {
    $sourceItemsDelete->execute($sourceItems);
}
