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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');

$categoryId = 333;
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode('catalog_product');
$defaultSetId = $objectManager->create(\Magento\Catalog\Model\Product::class)->getDefaultAttributeSetid();

$products = [
    [
        'type' => 'simple',
        'name' => 'search product 1',
        'sku' => '24 MB06',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => $defaultSetId,
        'website_ids' => [\Magento\Store\Model\Store::DISTRO_STORE_ID],
        'price' => 10,
        'category_id' => $categoryId,
        'meta_title' => 'Key Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'name' => 'search product 2',
        'sku' => '24 MB04',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => $defaultSetId,
        'website_ids' => [\Magento\Store\Model\Store::DISTRO_STORE_ID],
        'price' => 10,
        'category_id' => $categoryId,
        'meta_title' => 'Last Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'name' => 'search product 3',
        'sku' => '24 MB02',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => $defaultSetId,
        'website_ids' => [\Magento\Store\Model\Store::DISTRO_STORE_ID],
        'price' => 20,
        'category_id' => $categoryId,
        'meta_title' => 'First Title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
    [
        'type' => 'simple',
        'name' => 'search product 4',
        'sku' => '24 MB01',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'attribute_set' => $defaultSetId,
        'website_ids' => [\Magento\Store\Model\Store::DISTRO_STORE_ID],
        'price' => 30,
        'category_id' => $categoryId,
        'meta_title' => 'A title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description',
    ],
];

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement =  $objectManager->create(CategoryLinkManagementInterface::class);

$categoriesToAssign = [];

foreach ($products as $data) {
    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product
        ->setTypeId($data['type'])
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
