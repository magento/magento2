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

$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);

$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_CODE => 'source-1',
        SourceItemInterface::SKU => 'sku1',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'source-1',
        SourceItemInterface::SKU => 'sku2',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'source-2',
        SourceItemInterface::SKU => 'sku1',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'source-2',
        SourceItemInterface::SKU => 'sku2',
        SourceItemInterface::QUANTITY => 100,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
];

$sourceItems = [];
foreach ($sourcesItemsData as $sourceItemData) {
    $sourceItem = $sourceItemFactory->create();
    $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
    $sourceItems[] = $sourceItem;
}
$sourceItemsSave->execute($sourceItems);
