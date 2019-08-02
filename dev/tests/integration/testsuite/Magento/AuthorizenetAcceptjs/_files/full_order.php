<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/default_rollback.php';
$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

$objectManager = Bootstrap::getObjectManager();

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping')
    ->setStreet(['6161 West Centinela Avenue'])
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setShippingMethod('flatrate_flatrate');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('authorizenet_acceptjs');
$payment->setAuthorizationTransaction(true);
$payment->setAdditionalInformation('ccLast4', '1111');
$payment->setAdditionalInformation('opaqueDataDescriptor', 'mydescriptor');
$payment->setAdditionalInformation('opaqueDataValue', 'myvalue');

/** @var Item $orderItem */
$orderItem1 = $objectManager->create(Item::class);
$orderItem1
    ->setSku('simple')
    ->setName('Simple product')
    ->setQtyOrdered(1)
    ->setBasePrice(80)
    ->setPrice(80)
    ->setRowTotal(80)
    ->setProductType('simple');

/** @var Item $orderItem */
$orderItem2 = $objectManager->create(Item::class);
$orderItem2
    ->setSku('simple2')
    ->setName('Simple product2')
    ->setPrice(10)
    ->setQtyOrdered(2)
    ->setBasePrice(10)
    ->setPrice(10)
    ->setRowTotal(10)
    ->setProductType('simple');

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
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->addItem($orderItem1)
    ->addItem($orderItem2)
    ->setPayment($payment);

$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
return $orderRepository->save($order);
