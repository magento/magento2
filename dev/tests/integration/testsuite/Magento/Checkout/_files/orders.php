<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/customers.php');

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');
$payment->setAdditionalInformation('last_trans_id', '11122');
$payment->setAdditionalInformation('metadata', [
    'type' => 'free',
    'fraudulent' => false
]);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);

$order->setIncrementId(
    '100000001'
)->setState(
    \Magento\Sales\Model\Order::STATE_PROCESSING
)->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
)->setSubtotal(
    100
)->setGrandTotal(
    100
)->setBaseSubtotal(
    100
)->setBaseGrandTotal(
    100
)->setCustomerIsGuest(
    true
)->setCustomerId(
    null
)->setCustomerEmail(
    'unknown@example.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setStoreId(
    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId()
)->setPayment(
    $payment
);
$order->isObjectNew(true);
$order->save();


$order->setIncrementId(
    '100000002'
)->setState(
    \Magento\Sales\Model\Order::STATE_PROCESSING
)->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
)->setSubtotal(
    100
)->setGrandTotal(
    100
)->setBaseSubtotal(
    100
)->setBaseGrandTotal(
    100
)->setCustomerIsGuest(
    false
)->setCustomerId(
    1
)->setCustomerEmail(
    'customer1@example.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setStoreId(
    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId()
)->setPayment(
    $payment
);
$order->isObjectNew(true);
$order->save();


$order->setIncrementId(
    '100000003'
)->setState(
    \Magento\Sales\Model\Order::STATE_PROCESSING
)->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
)->setSubtotal(
    100
)->setGrandTotal(
    100
)->setBaseSubtotal(
    100
)->setBaseGrandTotal(
    100
)->setCustomerIsGuest(
    false
)->setCustomerId(
    2
)->setCustomerEmail(
    'customer2@example.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setStoreId(
    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId()
)->setPayment(
    $payment
);
$order->isObjectNew(true);
$order->save();
