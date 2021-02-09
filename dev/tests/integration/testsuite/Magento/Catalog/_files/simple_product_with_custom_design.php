<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\TestFramework\Helper\Bootstrap;

$product = Bootstrap::getObjectManager()->create(ProductFactory::class)->create();
$product->setTypeId('simple')
    ->setPageLayout('3columns')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product With Custom Design')
    ->setSku('simple_with_custom_design')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1])
    ->save();

$customDesignProduct = Bootstrap::getObjectManager()
    ->create(Product::class, ['data' => $product->getData()]);

$customDesignProduct->setUrlKey('custom-design-simple-product')
    ->setId(2)
    ->setRowId(2)
    ->setName('Custom Design Simple Product')
    ->setSku('custom-design-simple-product')
    ->setCustomDesign('Magento/blank')
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 24, 'is_in_stock' => 1])
    ->setQty(24)
    ->save();
