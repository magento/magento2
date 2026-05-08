<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var IndexerRegistry $indexRegistry */
$indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);

$model = $indexRegistry->get(CategoryProductIndexer::INDEXER_ID);
$model->reindexAll();
