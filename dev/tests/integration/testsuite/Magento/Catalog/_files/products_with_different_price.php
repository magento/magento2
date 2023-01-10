<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

$productPrices = [0, 0.01, 5, 9.99, 10];
$productTemplate = [
    'type' => 'simple',
    'name' => 'Product with price ',
    'sku' => 'search_product_price_',
    'status' => Status::STATUS_ENABLED,
    'visibility' => Visibility::VISIBILITY_BOTH,
    'attribute_set' => 4,
    'website_ids' => [1],
    'category_ids' => [1],
];

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
foreach ($productPrices as $price) {

    $sku =  $productTemplate['sku'] . $price;
    $name = $productTemplate['name'] . $price;

    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product
        ->setTypeId($productTemplate['type'])
        ->setAttributeSetId($productTemplate['attribute_set'])
        ->setWebsiteIds($productTemplate['website_ids'])
        ->setName($name)
        ->setSku($sku)
        ->setPrice($price)
        ->setVisibility($productTemplate['visibility'])
        ->setStatus($productTemplate['status'])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $productRepository->save($product);

    $categoryLinkManagement->assignProductToCategories($sku, $productTemplate['category_ids']);
}

$indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
$fulltextIndexer = $indexRegistry->get(Fulltext::INDEXER_ID);
$fulltextIndexer->reindexAll();
