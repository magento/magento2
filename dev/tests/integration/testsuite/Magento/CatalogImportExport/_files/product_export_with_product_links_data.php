<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** Create category  */
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
/** Create fixture store */
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store.php');
/** Create product with multiselect attribute */
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_with_multiselect_attribute.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple_ms_1');
$productModel = $objectManager->create(
    \Magento\Catalog\Model\Product::class
);

$productModel->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setName(
    'New Product'
)->setSku(
    'simple'
)->setPrice(
    10
)->setTierPrice(
    [0 => ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 8]]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setCategoryIds(
    []
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1]
)->setCanSaveCustomOptions(
    true
)->setCategoryIds(
    [333]
)->setUpSellLinkData(
    [$product->getId() => ['position' => 1]]
)->setCrossSellLinkData(
    [$product->getId() => ['position' => 2]]
)->setRelatedLinkData(
    [$product->getId() => ['position' => 3]]
)->save();
