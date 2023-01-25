<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/rollback_quote.php');

/** @var $product \Magento\Catalog\Model\Product */
$product1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product1->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product 1'
)->setSku(
    'Simple Product 1 sku'
)->setPrice(
    10
)->setDescription(
    'Description with <b>html tag</b>'
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setCategoryIds(
    [2]
)->setStockData(
    ['use_config_manage_stock' => 0]
)->setCanSaveCustomOptions(
    true
)->setHasOptions(
    true
);

$product2 = clone $product1;
$product2->setName('Simple Product 2')->setSku('Simple Product 2 sku')->save();
$product3 = clone $product1;
$product3->setName('Simple Product 3')->setSku('Simple Product 3 sku')->save();
$product4 = clone $product1;
$product4->setName('Simple Product 4')->setSku('Simple Product 4 sku')->save();
$product1->save();
