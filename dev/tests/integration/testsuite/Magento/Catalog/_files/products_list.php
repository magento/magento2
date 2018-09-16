<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Wrong Simple Product')
    ->setSku('wrong-simple')
    ->setPrice(300)
    ->setWeight(10)
    ->setSpecialPrice(90)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('First Simple Product')
    ->setSku('simple-249')
    ->setPrice(249.9)
    ->setSpecialPrice(153)
    ->setWeight(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setWeight(10)
    ->setName('Second Simple Product')
    ->setSku('simple-156')
    ->setPrice(156)
    ->setSpecialPrice(156)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();
