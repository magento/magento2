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
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Catalog/_files/product_virtual.php';
require __DIR__ . '/store.php';
$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('braintree')
    ->setLastTransId('00001');

/** @var Item $orderItem */
$orderItem1 = $objectManager->create(Item::class);
$orderItem1->setProductId($product->getId())
    ->setSku($product->getSku())
    ->setName($product->getName())
    ->setQtyOrdered(1)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType($product->getTypeId());

$orderAmount = 100;
$customerEmail = $billingAddress->getEmail();

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setCustomerIsGuest(true)
    ->setRemoteIp('127.0.0.1')
    ->setCreatedAt('2016-12-12T12:00:55+0000')
    ->setOrderCurrencyCode('USD')
    ->setBaseCurrencyCode('USD')
    ->setSubtotal($orderAmount)
    ->setGrandTotal($orderAmount)
    ->setBaseSubtotal($orderAmount)
    ->setBaseGrandTotal($orderAmount)
    ->setCustomerEmail($customerEmail)
    ->setBillingAddress($billingAddress)
    ->setStoreId($store->getId())
    ->addItem($orderItem1)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
