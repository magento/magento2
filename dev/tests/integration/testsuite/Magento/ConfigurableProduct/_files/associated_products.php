<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 3')
    ->setSku('simple_3')
    ->setPrice(10)
    ->setMetaTitle('meta title 3')
    ->setMetaKeyword('meta keyword 3')
    ->setMetaDescription('meta description 3')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(14)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 14')
    ->setSku('simple_14')
    ->setPrice(10)
    ->setMetaTitle('meta title 14')
    ->setMetaKeyword('meta keyword 14')
    ->setMetaDescription('meta description 14')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(15)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 15')
    ->setSku('simple_15')
    ->setPrice(10)
    ->setMetaTitle('meta title 15')
    ->setMetaKeyword('meta keyword 15')
    ->setMetaDescription('meta description 15')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(92)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 92')
    ->setSku('simple_92')
    ->setPrice(10)
    ->setMetaTitle('meta title 92')
    ->setMetaKeyword('meta keyword 92')
    ->setMetaDescription('meta description 92')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

