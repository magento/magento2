<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product2 \Magento\Catalog\Model\Product */
$product2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product2
    ->setTypeId('simple')
    ->setId(6)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product2')
    ->setSku('simple2')
    ->setPrice(10)
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setCategoryIds([2])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->save();
