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
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
/** @var DefaultSourceProviderInterface $defaultSourceProvider */
$defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);

/**
 * | *Sku*  | *Source Code* | *Qty* | *Info*        |
 * |--------|---------------|-------|---------------|
 * | VIRT-1 | eu-1          | 2     |               |
 * | VIRT-1 | eu-2          | 12    |               |
 * | VIRT-1 | eu-3          | 12    | out of stock  |
 * | VIRT-1 | eu-disabled   | 6     |               |
 * | VIRT-1 | us-1          | 10    |               |
 * | VIRT-2 | eu-1          | 22    |               |
 * | VIRT-2 | eu-2          | 12    |               |
 * | VIRT-2 | eu-3          | 4     | out of stock  |
 * | VIRT-2 | eu-disabled   | 6     |               |
 * | VIRT-2 | us-1          | 3     |               |
 * | VIRT-3 | eu-1          | 4.5   |               |
 * | VIRT-3 | eu-2          | 6.7   |               |
 * | VIRT-3 | eu-3          | 0     | out of stock  |
 * | VIRT-3 | eu-disabled   | 3.89  |               |
 * | VIRT-3 | us-1          | 3.12  |               |
 * | VIRT-4 | eu-1          | 1     |               |
 * | VIRT-4 | eu-2          | 0     |               |
 * | VIRT-4 | eu-3          | 1     | out of stock  |
 * | VIRT-4 | eu-disabled   | 1     |               |
 * | VIRT-4 | us-1          | 1     |               |
 *
 */
$sourcesItemsData = [
    // VIRT-1
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 2,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 6,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],

    // VIRT-2
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 22,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 4,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 6,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 3,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],

    // VIRT-3
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 4.5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 6.7,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 3.89,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 3.12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],

    // VIRT-4
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'VIRT-4',
        SourceItemInterface::QUANTITY => 1,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'VIRT-4',
        SourceItemInterface::QUANTITY => 0,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'VIRT-4',
        SourceItemInterface::QUANTITY => 1,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 1,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'VIRT-4',
        SourceItemInterface::QUANTITY => 1,
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
