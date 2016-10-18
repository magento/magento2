<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

require dirname(dirname(__DIR__)) . '/Catalog/_files/category.php';
require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';
require dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute.php';
require dirname(dirname(__DIR__)) . '/Catalog/_files/product_text_attribute.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Product $productModel */
$productModel = $objectManager->create(\Magento\Catalog\Model\Product::class);

$customOptions = [
    [
        'option_id' => null,
        'sort_order' => '0',
        'title' => 'Option 1',
        'type' => 'drop_down',
        'is_require' => 1,
        'values' => [
            1 => [
                'option_type_id' => null,
                'title' => 'Option 1 & Value 1"',
                'price' => '1.00',
                'price_type' => 'fixed'
            ],
            2 => [
                'option_type_id' => null,
                'title' => 'Option 1 & Value 2"',
                'price' => '2.00',
                'price_type' => 'fixed'
            ],
            3 => [
                'option_type_id' => null,
                'title' => 'Option 1 & Value 3"',
                'price' => '3.00',
                'price_type' => 'fixed'
            ]
        ]
    ],
    [
        'title' => 'test_option_code_2',
        'type' => 'field',
        'is_require' => true,
        'sort_order' => 1,
        'price' => 10.0,
        'price_type' => 'fixed',
        'sku' => 'sku1',
        'max_characters' => 10,
    ],
];

$productModel->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setName(
    'New Product'
)->setSku(
    'simple'
)->setPrice(
    10
)->addData(
    ['text_attribute' => '!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/']
)->setTierPrice(
    [0 => ['website_id' => 0, 'cust_group' => 0, 'price_qty' => 3, 'price' => 8]]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setCateroryIds(
    []
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setCanSaveCustomOptions(
    true
)->setCategoryIds(
    [333]
)->setUpSellLinkData(
    [$product->getId() => ['position' => 1]]
);

$options = [];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);

foreach ($customOptions as $option) {
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option */
    $option = $customOptionFactory->create(['data' => $option]);
    $option->setProductSku($productModel->getSku());

    $options[] = $option;
}

$productModel->setOptions($options)->save();
