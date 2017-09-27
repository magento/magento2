<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\TestFramework\Helper\Bootstrap;

$indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
$indexerProcessor->getIndexer()->setScheduled(true);
