<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$configurationFactory = Bootstrap::getObjectManager()->get(SourceItemConfigurationInterfaceFactory::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$configurationSave = Bootstrap::getObjectManager()->get(SourceItemConfigurationsSaveInterface::class);

$inventoryConfigurationData = [
    SourceItemConfigurationInterface::SOURCE_ITEM_ID => 1,
    SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1.0
];

$inventoryConfigurationItems = [];

/** @var SourceItemConfigurationInterface $inventoryConfiguration */
$inventoryConfiguration = $configurationFactory->create();
$dataObjectHelper->populateWithArray(
    $inventoryConfiguration,
    $inventoryConfigurationData,
    SourceItemConfigurationInterface::class
);

$inventoryConfigurationItems[] = $inventoryConfiguration;
$configurationSave->execute($inventoryConfigurationItems);
