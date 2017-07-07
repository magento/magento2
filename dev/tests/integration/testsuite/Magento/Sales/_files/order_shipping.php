<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order.php';

/** @var \Magento\Sales\Model\Order $order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
$order->loadByIncrementId('100000001');

$order->setData(
    'base_to_global_rate',
    2
)->setData(
    'base_shipping_amount',
    20
)->setData(
    'base_shipping_canceled',
    2
)->setData(
    'base_shipping_invoiced',
    20
)->setData(
    'base_shipping_refunded',
    3
)->setData(
    'is_virtual',
    0
)->save();

$orderItems = $order->getItems();
/** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
$orderItem = array_values($orderItems)[0];

/** @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItem */
$invoiceItem = $objectManager->create(\Magento\Sales\Api\Data\InvoiceItemCreationInterface::class);
$invoiceItem->setOrderItemId($orderItem->getItemId());
$invoiceItem->setQty($orderItem->getQtyOrdered());
/** @var \Magento\Sales\Api\InvoiceOrderInterface $invoiceOrder */
$invoiceOrder = $objectManager->create(\Magento\Sales\Api\InvoiceOrderInterface::class);
$invoiceOrder->execute($order->getEntityId(), false, [$invoiceItem]);

/** @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItem */
$shipmentItem = $objectManager->create(\Magento\Sales\Api\Data\ShipmentItemCreationInterface::class);
$shipmentItem->setOrderItemId($orderItem->getItemId());
$shipmentItem->setQty($orderItem->getQtyOrdered());
/** @var \Magento\Sales\Api\ShipOrderInterface $shipOrder */
$shipOrder = $objectManager->create(\Magento\Sales\Api\ShipOrderInterface::class);
$shipOrder->execute($order->getEntityId(), [$shipmentItem]);
