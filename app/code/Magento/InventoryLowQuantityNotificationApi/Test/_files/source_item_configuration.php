<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave */
$sourceItemConfigurationsSave = $objectManager->create(SourceItemConfigurationsSaveInterface::class);
/** @var SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory */
$sourceItemConfigurationFactory = $objectManager->get(SourceItemConfigurationInterfaceFactory::class);
$data = [
    [
        // This should not be showed because source is disabled
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemConfigurationInterface::SKU => 'SKU-1',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1000,
    ],
    [
        // This should be showed
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-1',
        SourceItemConfigurationInterface::SKU => 'SKU-1',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1000,
    ],
    [
        // This should be showed regardless product is disabled
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-2',
        SourceItemConfigurationInterface::SKU => 'SKU-3',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1000,
    ],
];

$sourceItemConfigurations = [];
foreach ($data as $entity) {
    /** @var SourceItemConfigurationInterface $sourceItemConfiguration */
    $sourceItemConfiguration = $sourceItemConfigurationFactory->create();
    $sourceItemConfiguration->setSourceCode($entity[SourceItemConfigurationInterface::SOURCE_CODE]);
    $sourceItemConfiguration->setSku($entity[SourceItemConfigurationInterface::SKU]);
    $sourceItemConfiguration->setNotifyStockQty($entity[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]);
    $sourceItemConfigurations[] = $sourceItemConfiguration;
}

$sourceItemConfigurationsSave->execute($sourceItemConfigurations);
