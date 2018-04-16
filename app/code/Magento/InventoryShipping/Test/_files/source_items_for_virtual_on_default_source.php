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
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
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
 * VIRT-1 - 33.5 qty
 * VIRT-2 - 30 qty
 * VIRT-3 - 2 qty
 * VIRT-2 - 6 qty (out of stock)
 */
$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_CODE => $defaultSourceProvider->getCode(),
        SourceItemInterface::SKU => 'VIRT-1',
        SourceItemInterface::QUANTITY => 33,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => $defaultSourceProvider->getCode(),
        SourceItemInterface::SKU => 'VIRT-2',
        SourceItemInterface::QUANTITY => 30,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => $defaultSourceProvider->getCode(),
        SourceItemInterface::SKU => 'VIRT-3',
        SourceItemInterface::QUANTITY => 2,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_CODE => $defaultSourceProvider->getCode(),
        SourceItemInterface::SKU => 'VIRT-4',
        SourceItemInterface::QUANTITY => 6,
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
