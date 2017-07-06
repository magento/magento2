<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order.php';
/** @var \Magento\Catalog\Model\Product $product */
/** @var \Magento\Sales\Model\Order $order */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$orderItems = [
    [
        'product_id' => 1,
        'order_id' => $order->getId(),
        'base_price' => 90,
        'price' => 90,
        'row_total' => 92,
        'product_type' => 'configurable'
    ],
    [
        'product_id' => 1,
        'base_price' => 100,
        'order_id' => $order->getId(),
        'price' => 100,
        'row_total' => 102,
        'product_type' => 'configurable'
    ],
    [
        'product_id' => 12,
        'base_price' => 110,
        'order_id' => $order->getId(),
        'price' => 110,
        'row_total' => 112,
        'product_type' => 'virtual'
    ],
    [
        'product_id' => 13,
        'base_price' => 123,
        'order_id' => $order->getId(),
        'price' => 123,
        'row_total' => 126,
        'product_type' => 'simple'
    ]
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
