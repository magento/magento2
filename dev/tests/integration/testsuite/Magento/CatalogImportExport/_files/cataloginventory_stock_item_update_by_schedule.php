<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\TestFramework\Helper\Bootstrap;

/** * @var $indexerProcessor Processor */
$indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
$indexerProcessor->getIndexer()->setScheduled(true);
