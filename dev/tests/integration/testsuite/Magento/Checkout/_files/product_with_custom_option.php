<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Checkout/_files/simple_product.php';
/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);

/** @var $product \Magento\Catalog\Model\Product */
$product->setCanSaveCustomOptions(
    true
)->setHasOptions(
    true
);

$oldOptions = [
    [
        'id' => 1,
        'option_id' => 0,
        'previous_group' => 'text',
        'title' => 'Test Field',
        'type' => 'field',
        'is_require' => 1,
        'sort_order' => 0,
        'price' => 1,
        'price_type' => 'fixed',
        'sku' => '1-text',
        'max_characters' => 100,
    ],
];

$customOptions = [];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory');

foreach ($oldOptions as $option) {
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create(['data' => $option]);
    $customOption->setProductSku($product->getSku());

    $customOptions[] = $customOption;
}

$product->setOptions($customOptions)->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);
$optionId = $product->getOptions()[0]->getOptionId();

$requestInfo = new \Magento\Framework\DataObject(['qty' => 1, 'options' => [$optionId => 'test']]);

require __DIR__ . '/../../Checkout/_files/cart.php';
