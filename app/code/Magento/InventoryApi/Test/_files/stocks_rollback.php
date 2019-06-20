<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
foreach ([10, 20, 30] as $stockId) {
    try {
        //Unassign sales channels from stocks in order to delete given stocks.
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $salesChannelFactory = Bootstrap::getObjectManager()->get(SalesChannelInterfaceFactory::class);
        $stock = $stockRepository->get($stockId);
        $extensionAttributes = $stock->getExtensionAttributes();
        $extensionAttributes->setSalesChannels([]);
        $stockRepository->save($stock);

        //Delete stock.
        $stockRepository->deleteById($stockId);
    } catch (NoSuchEntityException $e) {
        //Stock already removed
    }
}

$removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
$removeIndexData->execute([10, 20, 30]);
