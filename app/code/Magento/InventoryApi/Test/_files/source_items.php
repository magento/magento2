<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);

/**
 * SKU-1 - EU-source-1(id:10) - 5.5qty
 * SKU-1 - EU-source-2(id:20) - 3qty
 * SKU-1 - EU-source-3(id:30) - 10qty (out of stock)
 * SKU-1 - EU-source-4(id:40) - 10qty (disabled source)
 *
 * SKU-2 - US-source-1(id:30) - 5qty
 *
 * SKU-3 - EU-source-2(id:20) - 6qty (out of stock)
 */
$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 5.5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 3,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'SKU-2',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'SKU-3',
        SourceItemInterface::QUANTITY => 6,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'SKU-4',
        SourceItemInterface::QUANTITY => 6,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'SKU-6',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
];

$sourceItems = [];
foreach ($sourcesItemsData as $sourceItemData) {
    /** @var SourceItemInterface $source */
    $sourceItem = $sourceItemFactory->create();
    $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
    $sourceItems[] = $sourceItem;
}
$sourceItemsSave->execute($sourceItems);
