<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require 'order.php';
/** @var \Magento\Catalog\Model\Product $product */
/** @var \Magento\Sales\Model\Order $order */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_full_option_set.php';
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_url_key.php';
$orderItems[] = [
        'product_id' => $product->getId(),
        'base_price' => 123,
        'order_id' => $order->getId(),
        'price' => 123,
        'row_total' => 126,
        'product_type' => 'simple'
];

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_all_fields.php';
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_custom_attribute.php';
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

/** @var array $orderItemData */
foreach ($orderItems as $orderItemData) {
    /** @var $orderItem \Magento\Sales\Model\Order\Item */
    $orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Sales\Model\Order\Item::class
    );
    $orderItem
        ->setData($orderItemData)
        ->save();
}
