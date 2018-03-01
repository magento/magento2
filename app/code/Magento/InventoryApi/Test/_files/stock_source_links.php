<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterfaceFactory;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var StockSourceLinksSaveInterface $stockSourceLinksSave */
$stockSourceLinksSave = Bootstrap::getObjectManager()->get(StockSourceLinksSaveInterface::class);
/** @var StockSourceLinkInterfaceFactory $stockSourceLinkFactory */
$stockSourceLinkFactory = Bootstrap::getObjectManager()->get(StockSourceLinkInterfaceFactory::class);

/**
 * EU-source-1(code:eu-1) - EU-stock(id:10)
 * EU-source-2(code:eu-2) - EU-stock(id:10)
 * EU-source-3(code:eu-3) - EU-stock(id:10)
 * EU-source-disabled(code:eu-disabled) - EU-stock(id:10)
 *
 * US-source-1(code:us-1) - US-stock(id:20)
 *
 * EU-source-1(code:eu-1) - Global-stock(id:30)
 * EU-source-2(code:eu-2) - Global-stock(id:30)
 * EU-source-3(code:eu-3) - Global-stock(id:30)
 * EU-source-disabled(code:eu-disabled) - Global-stock(id:30)
 * US-source-1(code:us-1) - Global-stock(id:30)
 */

$linksData = [
    [
        StockSourceLinkInterface::STOCK_ID => 10,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
        StockSourceLinkInterface::PRIORITY => 1,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 10,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
        StockSourceLinkInterface::PRIORITY => 2,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 10,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-3',
        StockSourceLinkInterface::PRIORITY => 3,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 10,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-disabled',
        StockSourceLinkInterface::PRIORITY => 4,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 20,
        StockSourceLinkInterface::SOURCE_CODE => 'us-1',
        StockSourceLinkInterface::PRIORITY => 1,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 30,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
        StockSourceLinkInterface::PRIORITY => 5,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 30,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
        StockSourceLinkInterface::PRIORITY => 4,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 30,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-3',
        StockSourceLinkInterface::PRIORITY => 3,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 30,
        StockSourceLinkInterface::SOURCE_CODE => 'eu-disabled',
        StockSourceLinkInterface::PRIORITY => 2,
    ],
    [
        StockSourceLinkInterface::STOCK_ID => 30,
        StockSourceLinkInterface::SOURCE_CODE => 'us-1',
        StockSourceLinkInterface::PRIORITY => 1,
    ],
];

$links = [];
foreach ($linksData as $linkData) {
    /** @var StockSourceLinkInterface $link */
    $link = $stockSourceLinkFactory->create();
    $dataObjectHelper->populateWithArray($link, $linkData, StockSourceLinkInterface::class);
    $links[] = $link;
}
$stockSourceLinksSave->execute($links);
