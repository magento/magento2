<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\CategoryLinkManagementInterface;

$objectManager = Bootstrap::getObjectManager();

/** @var StoreInterface $defaultWebsite */
$defaultStoreView = $objectManager->get(StoreManagerInterface::class)->getDefaultStoreView();

/** @var CategoryInterface $categoryAnchor */
$categoryAnchor = $objectManager->create(CategoryInterface::class);
$categoryAnchor->isObjectNew(true);
$categoryAnchor
    ->setId(22)
    ->setIsAnchor(true)
    ->setStoreId($defaultStoreView->getId())
    ->setName('Category_Anchor')
    ->setParentId(2)
    ->setPath('1/2/22')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true);
$categoryAnchor->save();

/** @var CategoryInterface $categoryDefault */
$categoryDefault = $objectManager->create(CategoryInterface::class);
$categoryDefault->isObjectNew(true);
$categoryDefault
    ->setId(11)
    ->setIsAnchor(false)
    ->setStoreId($defaultStoreView->getId())
    ->setName('Category_Default')
    ->setParentId(22)
    ->setPath('1/2/22/11')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true);
$categoryDefault->save();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productTemplate = [
    'type' => 'simple',
    'sku' => 'product_anchor_',
    'status' => Status::STATUS_ENABLED,
    'visibility' => Visibility::VISIBILITY_BOTH,
    'price' => 1,
    'attribute_set' => 4,
    'website_ids' => [1],
    'category_ids' => [1],
];

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);

$products = [
    ['name' => 'Product1', 'categories'=> [11]],
    ['name' => 'Product2', 'categories' => [22]],
];

foreach ($products as $product) {
    $sku = $productTemplate['sku'] . $product['name'];

    /** @var Product $product */
    $newProduct = $objectManager->create(Product::class);
    $newProduct
        ->setTypeId($productTemplate['type'])
        ->setAttributeSetId($productTemplate['attribute_set'])
        ->setWebsiteIds($productTemplate['website_ids'])
        ->setName($product['name'])
        ->setSku($sku)
        ->setUrlKey(microtime(false))
        ->setPrice($productTemplate['website_ids'])
        ->setVisibility($productTemplate['visibility'])
        ->setStatus($productTemplate['status'])
        ->setStockData(['use_config_manage_stock' => 0]);
    $productRepository->save($newProduct);

    $categoryLinkManagement->assignProductToCategories($sku, $product['categories']);
}
