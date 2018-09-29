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

/**
 * EU-stock(id:10) - EU-website (code:eu_website)
 * US-stock(id:20) - US-website (code:us_website)
 * Global-stock(id:30) - Global-website (code:global_website)
 */
$salesChannelData = [10 => 'eu_website', 20 => 'us_website', 30 => 'global_website'];

foreach ($salesChannelData as $stockId => $websiteCode) {
    $stock = $stockRepository->get($stockId);
    $extensionAttributes = $stock->getExtensionAttributes();
    $salesChannels = $extensionAttributes->getSalesChannels();

    /** @var SalesChannelInterface $salesChannel */
    $salesChannel = $salesChannelFactory->create();
    $salesChannel->setCode($websiteCode);
    $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
    $salesChannels[] = $salesChannel;

    $extensionAttributes->setSalesChannels($salesChannels);
    $stockRepository->save($stock);
}
