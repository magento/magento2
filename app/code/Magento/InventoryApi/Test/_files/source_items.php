<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
/** @var SourceRepositoryInterface $sourceRepository */
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
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

$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_ID => $sources[0]->getSourceId(),
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => $sources[1]->getSourceId(),
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 3,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => $sources[2]->getSourceId(),
        SourceItemInterface::SKU => 'SKU-2',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],[
        SourceItemInterface::SOURCE_ID => $sources[3]->getSourceId(),
        SourceItemInterface::SKU => 'SKU-2',
        SourceItemInterface::QUANTITY => 10,
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
