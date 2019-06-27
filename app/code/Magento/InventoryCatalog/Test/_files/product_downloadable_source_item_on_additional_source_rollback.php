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

$sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
$sourceItemsDelete = Bootstrap::getObjectManager()->get(SourceItemsDeleteInterface::class);
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);

$searchCriteria = $searchCriteriaBuilder->addFilter(SourceItemInterface::SKU, 'downloadable-product', 'eq')->create();
$sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();

/**
 * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
 * In that case there is "if" which checks that "downloadable-product" still exists in database.
 */
if (!empty($sourceItems)) {
    $sourceItemsDelete->execute($sourceItems);
}
