<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Catalog/_files/multiple_products.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/store.php';

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping')
    ->setStreet(['6161 West Centinela Avenue', 'app. 33'])
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setShippingMethod('flatrate_flatrate');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('paypal_express')
    ->setLastTransId('00001')
    ->setCcLast4('1234')
    ->setCcExpMonth('01')
    ->setCcExpYear('21');

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$product1 = $productRepository->get('simple1');
/** @var Item $orderItem */
$orderItem1 = $objectManager->create(Item::class);
$orderItem1->setProductId($product1->getId())
    ->setSku($product1->getSku())
    ->setName($product1->getName())
    ->setQtyOrdered(1)
    ->setBasePrice($product1->getPrice())
    ->setPrice($product1->getPrice())
    ->setRowTotal($product1->getPrice())
    ->setProductType($product1->getTypeId());

$product2 = $productRepository->get('simple2');
/** @var Item $orderItem */
$orderItem2 = $objectManager->create(Item::class);
$orderItem2->setProductId($product2->getId())
    ->setSku($product2->getSku())
    ->setName($product2->getName())
    ->setPrice($product2->getPrice())
    ->setQtyOrdered(2)
    ->setBasePrice($product2->getPrice())
    ->setPrice($product2->getPrice())
    ->setRowTotal($product2->getPrice())
    ->setProductType($product2->getTypeId());

$orderAmount = 100;
$customerEmail = $billingAddress->getEmail();

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setCustomerId($customer->getId())
    ->setCustomerIsGuest(false)
    ->setRemoteIp('127.0.0.1')
    ->setCreatedAt(date('Y-m-d 00:00:55'))
    ->setOrderCurrencyCode('USD')
    ->setBaseCurrencyCode('USD')
    ->setSubtotal($orderAmount)
    ->setGrandTotal($orderAmount)
    ->setBaseSubtotal($orderAmount)
    ->setBaseGrandTotal($orderAmount)
    ->setCustomerEmail($customerEmail)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(10)
    ->setStoreId($store->getId())
    ->addItem($orderItem1)
    ->addItem($orderItem2)
    ->setPayment($payment)
    ->setQuoteId(1);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

$orderAmount2 = 50;
$payment2 = $objectManager->create(Payment::class);
$payment2->setMethod('checkmo');
/** @var Order $order2 */
$order2 = $objectManager->create(Order::class);
$order2->setIncrementId('100000005')
    ->setCustomerId($customer->getId())
    ->setCustomerIsGuest(false)
    ->setRemoteIp('127.0.0.1')
    ->setCreatedAt('2016-12-12T12:00:55+0000')
    ->setOrderCurrencyCode('USD')
    ->setBaseCurrencyCode('USD')
    ->setGrandTotal($orderAmount2)
    ->setBaseGrandTotal($orderAmount2)
    ->setCustomerEmail($customerEmail)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(10)
    ->setStoreId($store->getId())
    ->addItem($orderItem1)
    ->setPayment($payment2)
    ->setQuoteId(2);

$orderRepository->save($order2);
