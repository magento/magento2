<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

require 'products.php';
require 'orders.php';

$objectManager = Bootstrap::getObjectManager();

$order = Bootstrap::getObjectManager()->create(Order::class);
$order1 = clone $order->loadByIncrementId('100000001');
$order2 = clone $order->loadByIncrementId('100000002');
$order3 = clone $order->loadByIncrementId('100000003');

/** @var  Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->create(Magento\Catalog\Model\ProductRepository::class);
$product1 = $productRepository->get('Simple Product 1 sku');
$product2 = $productRepository->get('Simple Product 2 sku');
$product3 = $productRepository->get('Simple Product 3 sku');
$product4 = $productRepository->get('Simple Product 4 sku');

//@codingStandardsIgnoreStart
$productOptionValue = '{"info_buyRequest":{"uenc":"aHR0cDovL21hZ2VudG90MjIubG9jYWwvdGVzdC5odG1s","product":"%s","qty":1}}';
//@codingStandardsIgnoreEnd

$orderItems = [
    [
        'item_id' => 1,
        'product_id' => $product1->getId(),
        'order_id' => $order1->getId(),
        'base_price' => 90,
        'price' => 90,
        'row_total' => 92,
        'product_type' => 'simple',
        'product_options' => sprintf($productOptionValue, $product1->getId())
    ],
    [
        'item_id' => 2,
        'product_id' => $product2->getId(),
        'base_price' => 100,
        'order_id' => $order2->getId(),
        'price' => 100,
        'row_total' => 102,
        'product_type' => 'simple',
        'product_options' => sprintf($productOptionValue, $product2->getId())
    ],
    [
        'item_id' => 3,
        'product_id' => $product3->getId(),
        'base_price' => 110,
        'order_id' => $order3->getId(),
        'price' => 110,
        'row_total' => 112,
        'product_type' => 'simple',
        'product_options' => sprintf($productOptionValue, $product3->getId())
    ],
    [
        'item_id' => 4,
        'product_id' => $product4->getId(),
        'base_price' => 123,
        'order_id' => $order3->getId(),
        'price' => 123,
        'row_total' => 126,
        'product_type' => 'simple',
        'product_options' => sprintf($productOptionValue, $product4->getId())
    ],
];

/** @var $orderItem \Magento\Sales\Model\Order\Item */
$orderItem = Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Item::class
);
foreach ($orderItems as $orderItemData) {
    $orderItem->isObjectNew(true);
    /** @var array $orderItemData */
    $orderItem
        ->setData($orderItemData)
        ->save();
}
