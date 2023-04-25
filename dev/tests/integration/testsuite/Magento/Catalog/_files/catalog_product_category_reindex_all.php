<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var IndexerRegistry $indexRegistry */
$indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);

$model = $indexRegistry->get(ProductCategoryIndexer::INDEXER_ID);
$model->reindexAll();
