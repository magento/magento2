<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/order.php';

/** @var Order $order */
$payment = $order->getPayment();
$paymentInfoBlock = Bootstrap::getObjectManager()->get(Data::class)
    ->getInfoBlock($payment);
$payment->setBlockMock($paymentInfoBlock);

$items = [
    [
        'name' => 'item 1',
        'base_price' => 10,
        'price' => 10,
        'row_total' => 10,
        'product_type' => 'simple',
    ],
    [
        'name' => 'item 2',
        'base_price' => 20,
        'price' => 20,
        'row_total' => 20,
        'product_type' => 'simple',
    ],
    [
        'name' => 'item 3',
        'base_price' => 30,
        'price' => 30,
        'row_total' => 30,
        'product_type' => 'simple',
    ],
    [
        'name' => 'item 4',
        'base_price' => 40,
        'price' => 40,
        'row_total' => 40,
        'product_type' => 'simple',
    ],
    [
        'name' => 'item 5',
        'base_price' => 50,
        'price' => 50,
        'row_total' => 50,
        'product_type' => 'simple',
    ],
];

foreach ($items as $data) {
    /** @var OrderItem $orderItem */
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setName($data['name']);
    $orderItem->setProductId($product->getId());
    $orderItem->setBasePrice($data['base_price']);
    $orderItem->setPrice($data['price']);
    $orderItem->setRowTotal($data['row_total']);
    $orderItem->setProductType($data['product_type']);
    $orderItem->setQtyOrdered(10);
    $orderItem->setQtyInvoiced(5);
    $orderItem->setOriginalPrice(20);

    $order->addItem($orderItem);
    $order->save();
}

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = Bootstrap::getObjectManager()->get(ShipmentFactory::class)->create($order, $items);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);
$shipment->save();
