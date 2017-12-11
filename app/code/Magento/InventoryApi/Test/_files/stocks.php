<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockInterfaceFactory $stockFactory */
$stockFactory = Bootstrap::getObjectManager()->get(StockInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);

$stocksData = [
    [
        // define only required and needed for tests fields
        StockInterface::STOCK_ID => 10,
        StockInterface::NAME => 'EU-stock',
    ],
    [
        StockInterface::STOCK_ID => 20,
        StockInterface::NAME => 'US-stock',
    ],
    [
        StockInterface::STOCK_ID => 30,
        StockInterface::NAME => 'Global-stock',
    ],
];
foreach ($stocksData as $stockData) {
    /** @var StockInterface $stock */
    $stock = $stockFactory->create();
    $dataObjectHelper->populateWithArray($stock, $stockData, StockInterface::class);
    $stockRepository->save($stock);
}
