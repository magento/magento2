<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

include __DIR__ . '/../../Catalog/_files/category.php';

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

$products = [
    [
        'type' => 'simple',
        'id' => 201,
        'name' => 'search product 1',
        'sku' => '24 MB06',
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
        'id' => 202,
        'name' => 'search product 2',
        'sku' => '24 MB04',
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
        'id' => 203,
        'name' => 'search product 3',
        'sku' => '24 MB02',
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
        'id' => 204,
        'name' => 'search product 4',
        'sku' => '24 MB01',
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
];

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(CategoryLinkManagementInterface::class);

$categoriesToAssign = [];

foreach ($products as $data) {
    /** @var $product Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Product::class);
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
        ->setStockData(['use_config_manage_stock' => 0])
        ->save();

    $categoriesToAssign[$data['sku']][] = $data['category_id'];
}

foreach ($categoriesToAssign as $sku => $categoryIds) {
    $categoryLinkManagement->assignProductToCategories($sku, $categoryIds);
}
