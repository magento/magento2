<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
/** @var SalesChannelInterfaceFactory $salesChannelFactory */
$salesChannelFactory = Bootstrap::getObjectManager()->get(SalesChannelInterfaceFactory::class);

$websiteCodes = ['eu_website', 'us_website', 'global_website'];
$defaultStock = $stockRepository->get(1);
$extensionAttributes = $defaultStock->getExtensionAttributes();
$salesChannels = $extensionAttributes->getSalesChannels();

// reassign on Default Stock because website can't exists without link to any Stock
foreach ($websiteCodes as $websiteCode) {
    /** @var SalesChannelInterface $salesChannel */
    $salesChannel = $salesChannelFactory->create();
    $salesChannel->setCode($websiteCode);
    $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
    $salesChannels[] = $salesChannel;
}
$extensionAttributes->setSalesChannels($salesChannels);
$stockRepository->save($defaultStock);
