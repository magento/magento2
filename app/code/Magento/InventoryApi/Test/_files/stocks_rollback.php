<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Exception\NoSuchEntityException;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
foreach ([10, 20, 30] as $stockId) {
    try {
        $stockRepository->deleteById($stockId);
    } catch (NoSuchEntityException $e) {
        //Stock already removed
    }
}

$removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
$removeIndexData->execute([10, 20, 30]);
