<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Module\Manager;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
/** @var RemoveIndexData $removeIndexData */
$removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);

$stockIds = [];
foreach ($stockRepository->getList()->getItems() as $stock) {
    $stockIds[$stock->getStockId()] = $stock->getStockId();
}

/** @var Manager $moduleManager */
$moduleManager = Bootstrap::getObjectManager()->get(Manager::class);
// soft dependency in tests because we don't have possibility replace fixture from different modules
if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    /** @var DefaultStockProviderInterface $defaultStockProvider */
    $defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    unset($stockIds[$defaultStockProvider->getId()]);
}

$removeIndexData->execute(array_values($stockIds));
