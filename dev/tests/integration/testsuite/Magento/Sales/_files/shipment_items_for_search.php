<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\ShipmentItemRepositoryInterface;

require 'default_rollback.php';
require __DIR__ . '/order.php';

/** @var Order $order */
$payment = $order->getPayment();
$paymentInfoBlock = Bootstrap::getObjectManager()->get(Data::class)
    ->getInfoBlock($payment);
$payment->setBlockMock($paymentInfoBlock);

/** @var Shipment $shipment */
$shipment = Bootstrap::getObjectManager()->create(Shipment::class);
$shipment->setOrder($order);

/** @var Item $shipmentItem */
$shipmentItem = Bootstrap::getObjectManager()->create(Item::class);
$shipmentItem->setOrderItem($orderItem);
$shipment->addItem($shipmentItem);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);
$shipment->save();

/** @var ItemFactory $shipmentItemFactory */
$shipmentItemFactory = Bootstrap::getObjectManager()->create(ItemFactory::class);

$items = [
    [
        'name' => 'item 1',
        'base_price' => 10,
        'price' => 10,
        'row_total' => 10,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 2',
        'base_price' => 20,
        'price' => 20,
        'row_total' => 20,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 3',
        'base_price' => 30,
        'price' => 30,
        'row_total' => 30,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 4',
        'base_price' => 40,
        'price' => 40,
        'row_total' => 40,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 5',
        'base_price' => 50,
        'price' => 50,
        'row_total' => 50,
        'product_type' => 'simple',
        'qty' => 2,
        'qty_invoiced' => 20,
        'qty_refunded' => 2,
    ],
];

/** @var ShipmentItemRepositoryInterface $shipmentItemRepository */
$shipmentItemRepository = Bootstrap::getObjectManager()->get(ShipmentItemRepositoryInterface::class);

foreach ($items as $data) {
    /** @var OrderItem $orderItem */
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setProductId($product->getId())->setQtyOrdered(10);
    $orderItem->setBasePrice($data['base_price']);
    $orderItem->setPrice($data['price']);
    $orderItem->setRowTotal($data['row_total']);
    $orderItem->setProductType($data['product_type']);
    $orderItem->setQtyOrdered(100);
    $orderItem->setQtyInvoiced(10);
    $orderItem->setOriginalPrice(20);

    $order->addItem($orderItem);
    $order->save();

    /** @var Item $shipmentItem */
    $shipmentItem = $shipmentItemFactory->create();
    $shipmentItem->setShipment($shipment)
        ->setName($data['name'])
        ->setOrderItem($orderItem)
        ->setOrderItemId($orderItem->getItemId())
        ->setQty($data['qty'])
        ->setPrice($data['price']);
    $shipmentItemRepository->save($shipmentItem);
}
