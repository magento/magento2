<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Indexer $indexer */
$indexer = $objectManager->get(Indexer::class);
$indexer->load(State::INDEXER_ID);
$indexer->reindexAll();
