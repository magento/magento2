<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getObjectManager()->get(IndexerRegistry::class)->get(Processor::INDEXER_ID)->reindexAll();
