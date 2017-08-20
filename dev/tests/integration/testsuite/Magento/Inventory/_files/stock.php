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

// ---------------  Create 3 stock--------------------------
$stocksData = [
    [
        StockInterface::NAME => 'stock_1'
    ],
    [
        StockInterface::NAME => 'stock_2'
    ],
    [
        StockInterface::NAME => 'stock_3'
    ]
];

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var  StockInterfaceFactory $stockItemFactory */
$stockFactory = Bootstrap::getObjectManager()->get(StockInterfaceFactory::class);
/** @var StockRepositoryInterface $stockItemSave */
$stockItemSave = Bootstrap::getObjectManager()->get(Magento\InventoryApi\Api\StockRepositoryInterface::class);

foreach ($stocksData as $stockData) {
    /** @var stockInterface $stock */
    $stock = $stockFactory->create();
    $dataObjectHelper->populateWithArray($stock, $stockData, StockInterface::class);
    $stockItemSave->save($stock);
}
