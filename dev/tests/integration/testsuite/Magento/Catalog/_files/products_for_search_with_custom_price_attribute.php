<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_price_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_for_search.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productSkus = [
    'search_product_1' => 55,
    'search_product_2' => 110,
    'search_product_3' => 515,
    'search_product_4' => 1020,
    'search_product_5' => 1225
];
foreach ($productSkus as $sku => $price) {
    $product = $productRepository->get($sku, true, null, true);
    $product->setProductPriceAttribute($price);
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
