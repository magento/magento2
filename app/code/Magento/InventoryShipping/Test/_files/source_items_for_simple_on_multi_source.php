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
 * | simple | eu-1          | 2     |               |
 * | simple | eu-2          | 12    |               |
 * | simple | eu-3          | 12    | out of stock  |
 * | simple | eu-disabled   | 6     |               |
 * | simple | us-1          | 10    |               |
 *
 */
$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'simple',
        SourceItemInterface::QUANTITY => 2,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'simple',
        SourceItemInterface::QUANTITY => 12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-3',
        SourceItemInterface::SKU => 'simple',
        SourceItemInterface::QUANTITY => 12,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'simple',
        SourceItemInterface::QUANTITY => 6,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => 'us-1',
        SourceItemInterface::SKU => 'simple',
        SourceItemInterface::QUANTITY => 10,
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
