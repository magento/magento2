<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
/** @var RemoveIndexData $removeIndexData */
$removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);

$stockIds = [];
foreach ($stockRepository->getList()->getItems() as $stock) {
    $stockIds[] = $stock->getStockId();
}
$removeIndexData->execute($stockIds);
