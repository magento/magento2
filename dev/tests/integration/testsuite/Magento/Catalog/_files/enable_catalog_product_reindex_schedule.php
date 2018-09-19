<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Indexer\IndexerRegistry::class
)->get('catalog_category_product');
$model->setScheduled(true);

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Indexer\IndexerRegistry::class
)->get('catalog_product_category');
$model->setScheduled(true);
