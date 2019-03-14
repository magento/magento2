<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemsConfigurationInterface;

/** @var DeleteSourceItemsConfigurationInterface $deleteSourceItemsConfiguration */
$deleteSourceItemsConfiguration = Bootstrap::getObjectManager()->get(DeleteSourceItemsConfigurationInterface::class);

/** @var SourceItemInterfaceFactory $sourceItemInterfaceFactory */
$sourceItemInterfaceFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);

$sourceItems = [
    $sourceItemInterfaceFactory->create([
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::SKU => 'SKU-1'
    ]),
    $sourceItemInterfaceFactory->create([
        SourceItemInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemInterface::SKU => 'SKU-1'
    ]),
    $sourceItemInterfaceFactory->create([
        SourceItemInterface::SOURCE_CODE => 'eu-2',
        SourceItemInterface::SKU => 'SKU-3'
    ])
];

$deleteSourceItemsConfiguration->execute($sourceItems);
