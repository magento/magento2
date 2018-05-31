<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

include __DIR__ . '/categories_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory */
$indexerCollectionFactory = $objectManager->get(\Magento\Indexer\Model\Indexer\CollectionFactory::class);
$indexerCollection = $indexerCollectionFactory->create();
$indexers = $indexerCollection->getItems();
foreach ($indexers as $indexer) {
    $indexer->reindexAll();
}
