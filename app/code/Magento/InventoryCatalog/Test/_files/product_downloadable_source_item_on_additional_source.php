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

$sourceItemData = [
    SourceItemInterface::SOURCE_CODE => 'eu-1',
    SourceItemInterface::SKU => 'downloadable-product',
    SourceItemInterface::QUANTITY => 100,
    SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
];
$sourceItems = [];
$sourceItem = $sourceItemFactory->create();
$dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
$sourceItems[] = $sourceItem;
$sourceItemsSave->execute($sourceItems);
