<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include 'category.php';

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$products = [
    [
        'type' => 'simple',
        'id' => 101,
        'name' => 'search product 1',
        'sku' => 'search_product_1',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => 4,
        'website_ids' => [1],
        'price' => 10,
        'category_id' => 333,
        'meta_title' => 'Key Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'id' => 102,
        'name' => 'search product 2',
        'sku' => 'search_product_2',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => 4,
        'website_ids' => [1],
        'price' => 10,
        'category_id' => 333,
        'meta_title' => 'Last Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'id' => 103,
        'name' => 'search product 3',
        'sku' => 'search_product_3',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => 4,
        'website_ids' => [1],
        'price' => 20,
        'category_id' => 333,
        'meta_title' => 'First Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'id' => 104,
        'name' => 'search product 4',
        'sku' => 'search_product_4',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => 4,
        'website_ids' => [1],
        'price' => 30,
        'category_id' => 333,
        'meta_title' => 'A title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'id' => 105,
        'name' => 'search product 5',
        'sku' => 'search_product_5',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => 4,
        'website_ids' => [1],
        'price' => 40,
        'category_id' => 333,
        'meta_title' => 'meta title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
];

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);

$categoriesToAssign = [];

foreach ($products as $data) {
    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product
        ->setTypeId($data['type'])
        ->setId($data['id'])
        ->setAttributeSetId($data['attribute_set'])
        ->setWebsiteIds($data['website_ids'])
        ->setName($data['name'])
        ->setSku($data['sku'])
        ->setPrice($data['price'])
        ->setMetaTitle($data['meta_title'])
        ->setMetaKeyword($data['meta_keyword'])
        ->setMetaDescription($data['meta_keyword'])
        ->setVisibility($data['visibility'])
        ->setStatus($data['status'])
        ->setStockData(['use_config_manage_stock' => 0]);
    $productRepository->save($product);

    $categoriesToAssign[$data['sku']][] = $data['category_id'];
}

foreach ($categoriesToAssign as $sku => $categoryIds) {
    $categoryLinkManagement->assignProductToCategories($sku, $categoryIds);
}
