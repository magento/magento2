<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/../../Framework/Search/_files/products.php';
use Magento\Catalog\Api\ProductRepositoryInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    333
)->setCreatedAt(
    '2019-08-27 11:05:07'
)->setName(
    'Colorful Category'
)->setParentId(
    2
)->setPath(
    '1/2/300'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$defaultAttributeSet = $objectManager->get(Magento\Eav\Model\Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Red White and Blue striped Shoes')
    ->setSku('red white and blue striped shoes')
    ->setPrice(40)
    ->setWeight(8)
    ->setDescription('Red white and blue flip flops at <b>one</b>')
    ->setMetaTitle('Multi colored shoes meta title')
    ->setMetaKeyword('red, white,flip-flops, women, kids')
    ->setMetaDescription('flip flops women kids meta description')
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$skus = ['green_socks', 'white_shorts','red_trousers','blue_briefs','grey_shorts', 'red white and blue striped shoes' ];
$products = [];
foreach ($skus as $sku) {
    $products = $productRepository->get($sku);
}
/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
        $categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
foreach ($products as $product) {
    $categoryLinkManagement->assignProductToCategories(
        $product->getSku(),
        [300]
    );
}
