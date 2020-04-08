<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\CacheCleaner;

require_once __DIR__ . '/products_for_search.php';
require_once __DIR__ . '/product_boolean_attribute.php';

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
