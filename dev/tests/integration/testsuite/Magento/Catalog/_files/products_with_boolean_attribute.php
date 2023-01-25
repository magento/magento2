<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_for_search.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_boolean_attribute.php');

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$yesIds = [101, 102, 104];
$noIds = [103, 105];

foreach ($yesIds as $id) {
    $product = $productRepository->getById($id);
    $product->setBooleanAttribute(1);
    $productRepository->save($product);
}
foreach ($noIds as $id) {
    $product = $productRepository->getById($id);
    $product->setBooleanAttribute(0);
    $productRepository->save($product);
}
CacheCleaner::cleanAll();
/** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
$indexerCollection = $objectManager->get(\Magento\Indexer\Model\Indexer\Collection::class);
$indexerCollection->load();
/** @var \Magento\Indexer\Model\Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
