<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory */
$sourceItemConfigurationFactory = Bootstrap::getObjectManager()->get(SourceItemConfigurationInterfaceFactory::class);
/** @var SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave */
$sourceItemConfigurationsSave = Bootstrap::getObjectManager()->get(SourceItemConfigurationsSaveInterface::class);

$sourceItemConfigurationsData = [
    [
        // for disabled source
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-disabled',
        SourceItemConfigurationInterface::SKU => 'SKU-1',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1000,
    ],
    [
        // notify_stock_qty > quantity
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-1',
        SourceItemConfigurationInterface::SKU => 'SKU-1',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 5.6,
    ],
    [
        // This should not be showed in status out of stock
        SourceItemConfigurationInterface::SOURCE_CODE => 'eu-2',
        SourceItemConfigurationInterface::SKU => 'SKU-3',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1000,
    ],
    [
        // notify_stock_qty < quantity
        SourceItemConfigurationInterface::SOURCE_CODE => 'us-1',
        SourceItemConfigurationInterface::SKU => 'SKU-2',
        SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 4.5,
    ],
];

$sourceItemConfigurations = [];
foreach ($sourceItemConfigurationsData as $sourceItemConfigurationData) {
    /** @var SourceItemConfigurationInterface $sourceItemConfiguration */
    $sourceItemConfiguration = $sourceItemConfigurationFactory->create();
    $dataObjectHelper->populateWithArray(
        $sourceItemConfiguration,
        $sourceItemConfigurationData,
        SourceItemConfigurationInterface::class
    );
    $sourceItemConfigurations[] = $sourceItemConfiguration;
}

$sourceItemConfigurationsSave->execute($sourceItemConfigurations);
