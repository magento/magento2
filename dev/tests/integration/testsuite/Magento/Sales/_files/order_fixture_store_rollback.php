<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_duplicated_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/core_fixturestore_rollback.php');

Bootstrap::getObjectManager()->get(IndexerRegistry::class)
    ->get(FulltextIndexer::INDEXER_ID)
    ->reindexAll();
