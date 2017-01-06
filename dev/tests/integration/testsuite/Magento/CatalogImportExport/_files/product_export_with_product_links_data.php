<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Create category  */
require dirname(dirname(__DIR__)) . '/Catalog/_files/category.php';
/** Create fixture store */
require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';
/** Create product with mulselect attribute */
require dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute.php';

$productModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
)->setCateroryIds(
    []
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
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
