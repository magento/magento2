<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockIndexerProcessor;
use Magento\TestFramework\Helper\Bootstrap;

$indexerProcessor = Bootstrap::getObjectManager()->get(StockIndexerProcessor::class);
$indexerProcessor->getIndexer()->setScheduled(false);
