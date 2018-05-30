<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/categories_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Indexer\Category\Product\Processor $categoryProductIndexer */
$categoryProductIndexer = $objectManager->get(
    \Magento\Catalog\Model\Indexer\Category\Product\Processor::class
);
$categoryProductIndexer->reindexAll();

/** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor $inventoryIndexer */
$inventoryIndexer = $objectManager->get(
    \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
);
$inventoryIndexer->reindexAll();
