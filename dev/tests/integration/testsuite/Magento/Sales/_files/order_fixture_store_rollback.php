<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

require __DIR__ . '/default_rollback.php';
require __DIR__ . '/../../Catalog/_files/product_simple_duplicated_rollback.php';
require __DIR__ . '/../../Store/_files/core_fixturestore_rollback.php';

Bootstrap::getObjectManager()->get(IndexerRegistry::class)
    ->get(FulltextIndexer::INDEXER_ID)
    ->reindexAll();
