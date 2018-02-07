<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setName(
    'Simple Up Sell'
)->setSku(
    'simple'
)->setPrice(
    100
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->save();

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
$productLink = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Api\Data\ProductLinkInterface'
);
$productLink->setSku('simple_with_upsell');
$productLink->setLinkedProductSku('simple');
$productLink->setPosition(1);
$productLink->setLinkType('upsell');

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    2
)->setAttributeSetId(
    4
)->setName(
    'Simple Product With Up Sell'
)->setSku(
    'simple_with_upsell'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setProductLinks(
    [$productLink]
)->save();
