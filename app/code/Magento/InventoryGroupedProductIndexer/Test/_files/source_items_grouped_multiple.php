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

$objectManager = Bootstrap::getObjectManager();

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = $objectManager->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = $objectManager->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = $objectManager->get(SourceItemsSaveInterface::class);

/*
 * SKU          us-1    eu-1    eu-2    eu-3
 * simple_11    100     100     100     0
 * simple_22    100     100     0       0
 */

$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'simple_11',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'simple_11',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'simple_11',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'simple_11',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'simple_22',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'simple_22',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'simple_22',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'simple_22',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
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
