<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Create category */
require dirname(dirname(__DIR__)) . '/Catalog/_files/category.php';
/** Create category with special chars */
require dirname(dirname(__DIR__)) . '/Catalog/_files/catalog_category_with_slash.php';
/** Create fixture store */
require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';
/** Create product with multiselect attribute and values */
require dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute.php';
/** Create dummy text attribute */
require dirname(dirname(__DIR__)) . '/Catalog/_files/product_text_attribute.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Product $productModel */
$productModel = $objectManager->create(\Magento\Catalog\Model\Product::class);

$productModel->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('New Product')
    ->setSku('simple &quot;1&quot;')
    ->setPrice(10)
    ->addData(['text_attribute' => '!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/›ƒª'])
    ->setTierPrice([0 => ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 8]])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setCategoryIds([333, 3331]);

$productModel->setOptions([])->save();
