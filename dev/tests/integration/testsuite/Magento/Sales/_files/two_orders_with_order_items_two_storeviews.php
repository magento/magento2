<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;

require 'default_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Store/_files/second_store.php';
/** @var \Magento\Catalog\Model\Product $product */

$addressData = include __DIR__ . '/address_data.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$secondStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Store\Model\Store::class);

$billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');
$customerIdFromFixture = 1;
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation(
        'metadata',
        [
            'type' => 'free',
            'fraudulent' => false,
        ]
    );

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple')
    ->setName($product->getName())
    ->setSku($product->getSku());

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(110)
    ->setOrderCurrencyCode("USD")
    ->setShippingAmount(10.0)
    ->setBaseShippingAmount(10.0)
    ->setTaxAmount(5.0)
    ->setGrandTotal(100)
    ->setBaseSubtotal(10)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(false)
    ->setCustomerId($customerIdFromFixture)
    ->setCustomerEmail('customer@null.com')
    ->setOrderCurrencyCode('USD')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->addItem($orderItem)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);

/** @var Payment $payment */
$secondPayment = $objectManager->create(Payment::class);
$secondPayment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation(
        'metadata',
        [
            'type' => 'free',
            'fraudulent' => false,
        ]
    );

/** @var OrderItem $orderItem */
$secondOrderItem = $objectManager->create(OrderItem::class);
$secondOrderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple')
    ->setName($product->getName())
    ->setSku($product->getSku());

/** @var Order $order */
$secondOrder = $objectManager->create(Order::class);
$secondOrder->setIncrementId('100000002')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($secondOrder->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(110)
    ->setOrderCurrencyCode("USD")
    ->setShippingAmount(10.0)
    ->setBaseShippingAmount(10.0)
    ->setTaxAmount(5.0)
    ->setGrandTotal(100)
    ->setBaseSubtotal(110)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(false)
    ->setCustomerId($customerIdFromFixture)
    ->setCustomerEmail('customer@null.com')
    ->setOrderCurrencyCode('USD')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($secondStore->load('fixture_second_store')->getId())
    ->addItem($secondOrderItem)
    ->setPayment($secondPayment);
$orderRepository->save($secondOrder);
