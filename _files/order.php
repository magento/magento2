<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Paypal\Model\Config as PaypalConfig;

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setid(null)
    ->setAddressType('shipping')
    ->setShippingMethod('flatrate_flatrate');

$payment = $objectManager->create(Payment::class);
$payment->setMethod(PaypalConfig::METHOD_WPP_EXPRESS);

/** @var Item $orderItem */
$orderItem = $objectManager->create(Item::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(1)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType($product->getTypeId());

$orderAmount = 100;
$customerEmail = $billingAddress->getEmail();

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setCreatedAt('2016-12-12T12:00:55+0000')
    ->setBaseGrandTotal($orderAmount)
    ->setSubtotal($orderAmount)
    ->setGrandTotal($orderAmount)
    ->setBaseSubtotal($orderAmount)
    ->setBaseGrandTotal($orderAmount)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail($customerEmail)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(10)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->addItem($orderItem)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

$shipmentItem = $objectManager->create(\Magento\Sales\Model\Order\Shipment\Item::class);
$shipmentItem->setOrderItem($orderItem);

/** @var \Magento\Sales\Model\Order\Shipment $shipment */
$shipment = $objectManager->create(\Magento\Sales\Model\Order\Shipment::class);
$shipment->setOrder($order)
    ->addItem($shipmentItem)
    ->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW)
    ->save();
