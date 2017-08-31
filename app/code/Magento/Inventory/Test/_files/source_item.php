<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceRepositoryInterface $sourceRepository */
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(Magento\Framework\Api\SearchCriteriaBuilder::class);
$sortOrderBuilder = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\SortOrderBuilder::class);
/*
$nameList = ['source-name-1', 'source-name-2', 'source-name-3', 'source-name-4', 'source-name-5'];

$sortOrder = $sortOrderBuilder
    ->setField(SourceInterface::NAME)
    ->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC)
    ->create();

$searchCriteria = $searchCriteriaBuilder
    ->addFilter(SourceInterface::NAME, $nameList, 'in')
    ->addSortOrder($sortOrder)
    ->create();
$sourceList = array_values($sourceRepository->getList($searchCriteria)->getItems());
*/

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);

$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_ID => 1,
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 2,
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 3,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 3,
        SourceItemInterface::SKU => 'SKU-2',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
];
$sourceItems = [];
foreach ($sourcesItemsData as $sourceItemData) {
    /** @var SourceInterface $source */
    $sourceItem = $sourceItemFactory->create();
    $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
    $sourceItems[] = $sourceItem;
}
$sourceItemsSave->execute($sourceItems);
