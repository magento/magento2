<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        StockInterface::STOCK_ID => 1,
        StockInterface::NAME => 'stock-name-1',
    ],
    [
        StockInterface::STOCK_ID => 2,
        StockInterface::NAME => 'stock-name-2',
    ],
    [
        StockInterface::STOCK_ID => 3,
        StockInterface::NAME => 'stock-name-3',
    ],
    [
        StockInterface::STOCK_ID => 4,
        StockInterface::NAME => 'stock-name-4',
    ],
];
foreach ($stocksData as $stockData) {
    /** @var StockInterface $stock */
    $stock = $stockFactory->create();
    $dataObjectHelper->populateWithArray($stock, $stockData, StockInterface::class);
    $stockRepository->save($stock);
}
