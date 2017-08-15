<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemSaveInterface;




// ---------------  Create 10 source items for the 5 sources ---------------------------
$sourcesItemData = [
    [
        SourceItemInterface::SOURCE_ID => 1,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 2,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 20,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 3,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 30,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 4,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ], [
        SourceItemInterface::SOURCE_ID => 5,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 1,
        SourceItemInterface::SKU => 'inventory_1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 2,
        SourceItemInterface::SKU => 'inventory_2',
        SourceItemInterface::QUANTITY => 30,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 3,
        SourceItemInterface::SKU => 'inventory_2',
        SourceItemInterface::QUANTITY => 50,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ],
    [
        SourceItemInterface::SOURCE_ID => 4,
        SourceItemInterface::SKU => 'inventory_2',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ], [
        SourceItemInterface::SOURCE_ID => 5,
        SourceItemInterface::SKU => 'inventory_2',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK
    ]
];

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var  SourceItemInterfaceFactory$sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);

$sourceItemList = [];
foreach ($sourcesItemData as $sourceItemData) {
    /** @var SourceInterface $source */
    $sourceItem = $sourceItemFactory->create();
    $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
    $sourceItemList[] = $sourceItem;
}
/** @var  SourceItemSaveInterface $sourceItemSave */
$sourceItemSave = Bootstrap::getObjectManager()->get(SourceItemSaveInterface::class);
$sourceItemSave->execute($sourceItemList);