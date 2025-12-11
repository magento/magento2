<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var IndexerRegistry $indexRegistry */
$indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);

$model = $indexRegistry->get('catalog_category_product');
$model->setScheduled(false);

$model = $indexRegistry->get('catalog_product_category');
$model->setScheduled(false);
