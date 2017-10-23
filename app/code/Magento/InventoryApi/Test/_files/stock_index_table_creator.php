<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\Framework\App\ResourceConnection;

$indexNameBuilder = Bootstrap::getObjectManager()
    ->create(\Magento\Inventory\Indexer\IndexNameBuilder::class);
$indexStructureHandler = Bootstrap::getObjectManager()
    ->create(\Magento\Inventory\Indexer\StockItem\IndexStructure::class);

$stocksData = [
    [
        StockInterface::STOCK_ID => 10,
    ],
    [
        StockInterface::STOCK_ID => 20,
    ],
    [
        StockInterface::STOCK_ID => 30,
    ],
];

foreach ($stocksData as $stockData) {
    $mainIndexName = $indexNameBuilder
        ->setIndexId(\Magento\Inventory\Indexer\StockItemIndexerInterface::INDEXER_ID)
        ->addDimension('stock_', $stockData[StockInterface::STOCK_ID])
        ->setAlias(\Magento\Inventory\Indexer\Alias::ALIAS_MAIN)
        ->build();
    if (!$indexStructureHandler->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
        $indexStructureHandler->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
    }
}