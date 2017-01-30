<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Checkout/_files/simple_product.php';
/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);

/** @var $product \Magento\Catalog\Model\Product */
$product->setCanSaveCustomOptions(
    true
)->setProductOptions(
    [
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
    ]
)->setHasOptions(
    true
)->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);
$optionId = key($product->getOptions());

$requestInfo = new \Magento\Framework\DataObject(['qty' => 1, 'options' => [$optionId => 'test']]);

require __DIR__ . '/../../Checkout/_files/cart.php';
