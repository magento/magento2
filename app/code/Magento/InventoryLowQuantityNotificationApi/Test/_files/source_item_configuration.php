<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory */
$sourceItemConfigurationFactory = Bootstrap::getObjectManager()->get(SourceItemConfigurationInterfaceFactory::class);
/** @var SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave */
$sourceItemConfigurationsSave = Bootstrap::getObjectManager()->get(SourceItemConfigurationsSaveInterface::class);

$InventoryLowQuantityNotificationData = [
    SourceItemConfigurationInterface::SOURCE_CODE => 'eu-1',
    SourceItemConfigurationInterface::SKU => 'SKU-1',
    SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 2.000,
];

/** @var SourceItemConfigurationInterface $sourceItemConfiguration */
$sourceItemConfiguration = $sourceItemConfigurationFactory->create();
$dataObjectHelper->populateWithArray(
    $sourceItemConfiguration,
    $InventoryLowQuantityNotificationData,
    SourceItemConfigurationInterface::class
);
$sourceItemConfigurationsSave->execute([$sourceItemConfiguration]);
