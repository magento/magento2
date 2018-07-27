<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->save();

$customDesignProduct = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class, ['data' => $product->getData()]);

$customDesignProduct->setUrlKey('custom-design-simple-product')
    ->setId(2)
    ->setRowId(2)
    ->setSku('custom-design-simple-product')
    ->setCustomDesign('Magento/blank')
    ->save();
