<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/multiselect_attribute_with_source_model_rollback.php';

use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Remove all products as strategy of isolation process
 */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product */
$productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product')
    ->getCollection();

foreach ($productCollection as $product) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(IndexerRegistry::class)
    ->get(Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID)
    ->reindexAll();
