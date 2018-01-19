<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\TestFramework\Helper\Bootstrap;

/** @var IndexerInterface $indexer */
$indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
$indexer->load(InventoryIndexer::INDEXER_ID);
$indexer->reindexAll();
