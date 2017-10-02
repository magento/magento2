<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Create category */
require dirname(dirname(__DIR__)) . '/Catalog/_files/category.php';
/** Create fixture store */
require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';
/** Create product with multiselect attribute and values */
require dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute.php';
/** Create dummy text attribute */
require dirname(dirname(__DIR__)) . '/Catalog/_files/text_attribute.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
            ],
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

$productModel->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('New Product')
    ->setSku('simple')
    ->setPrice(10)
    ->addData(['text_attribute' => '!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/'])
    ->setTierPrice(
        [
            0 => [
                'website_id' => 0,
                'cust_group' => 0,
                'price_qty' => 3,
                'price' => 8
            ]
        ]
    )->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCateroryIds([])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setCategoryIds([333])
    ->setUpSellLinkData([$product->getId() => ['position' => 1]]);

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
