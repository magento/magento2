<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/multiselect_attribute_with_source_model_rollback.php'
);
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
