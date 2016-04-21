<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->removeSharedInstance('Magento\Catalog\Model\ProductRepository');
$objectManager->removeSharedInstance('Magento\Catalog\Model\Product\Option\Repository');
$objectManager->removeSharedInstance('Magento\Catalog\Model\Product\Option\SaveHandler');

$productRepository = $objectManager->get('Magento\Catalog\Model\ProductRepository');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product With Custom Options'
)->setSku(
    'simple_dropdown_option'
)->setPrice(
    200
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setCanSaveCustomOptions(
    true
)->setStockData(
    [
        'qty' => 0,
        'is_in_stock' => 0
    ]
);

$options = [
    [
        'title' => 'drop_down option',
        'type' => 'drop_down',
        'is_require' => true,
        'sort_order' => 4,
        'values' => [
            [
                'title' => 'drop_down option 1',
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'drop_down option 1 sku',
                'sort_order' => 1,
            ],
            [
                'title' => 'drop_down option 2',
                'price' => 20,
                'price_type' => 'percent',
                'sku' => 'drop_down option 2 sku',
                'sort_order' => 2,
            ],
        ],
    ]
];

$customOptions = [];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory');
$optionValueFactory = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory');

foreach ($options as $option) {
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create(['data' => $option]);
    $customOption->setProductSku($product->getSku());
    if (isset($option['values'])) {
        $values = [];
        foreach ($option['values'] as $value) {
            $value = $optionValueFactory->create(['data' => $value]);
            $values[] = $value;
        }
        $customOption->setValues($values);
    }
    $customOptions[] = $customOption;
}

$product->setOptions($customOptions);
$product->save();
