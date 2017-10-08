<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\ResourceConnection;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);

/**
 * SKU-1 - EU-source-1(id:1) - 5.5qty
 * SKU-1 - EU-source-2(id:2) - 3qty
 * SKU-1 - EU-source-3(id:3) - 10qty (out of stock)
 * SKU-1 - EU-source-4(id:4) - 10qty (disabled source)
 *
 * SKU-2 - US-source-1(id:3) - 5qty
 */
$sourcesItemsData = [
    [
        SourceItemInterface::SOURCE_ID => 1, // EU-source-1
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 5.5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 2, // EU-source-2
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 3,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 3, // EU-source-3
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 4, // EU-source-disabled
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 10,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
    [
        SourceItemInterface::SOURCE_ID => 5, // US-source-1
        SourceItemInterface::SKU => 'SKU-2',
        SourceItemInterface::QUANTITY => 5,
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ],
];

$resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
/** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
$connection = $resourceConnection->getConnection();
$connection->query('ALTER TABLE inventory_source_item AUTO_INCREMENT 1;');

$sourceItems = [];
foreach ($sourcesItemsData as $sourceItemData) {
    /** @var SourceItemInterface $source */
    $sourceItem = $sourceItemFactory->create();
    $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
    $sourceItems[] = $sourceItem;
}
$sourceItemsSave->execute($sourceItems);
