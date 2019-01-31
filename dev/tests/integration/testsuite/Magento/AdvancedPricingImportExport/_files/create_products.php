<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$productModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);

$productModel->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('AdvancedPricingSimple 1')
    ->setSku('AdvancedPricingSimple 1')
    ->setPrice(321)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setIsObjectNew(true)
    ->save();

$productModel->setName('AdvancedPricingSimple 2')
    ->setId(null)
    ->setUrlKey(null)
    ->setSku('AdvancedPricingSimple 2')
    ->setPrice(654)
    ->setIsObjectNew(true)
    ->save();
