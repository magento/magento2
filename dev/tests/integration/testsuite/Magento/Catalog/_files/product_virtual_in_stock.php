<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
    ->setId(21)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Virtual Product')
    ->setSku('virtual-product')
    ->setPrice(10)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['is_in_stock' => 1, 'qty' => 10, 'manage_stock' => 1])
    ->save();
