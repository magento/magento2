<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/order.php';
include __DIR__ . '/../../../Magento/Customer/_files/two_customers.php';

$customerIdFromFixture = 1;

$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();

$payment2 = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment2->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId('100000002')
    ->setState(
        \Magento\Sales\Model\Order::STATE_PROCESSING
    )->setStatus(
        $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
    )->setSubtotal(
        100
    )->setBaseSubtotal(
        100
    )->setBaseGrandTotal(
        100
    )->setCustomerIsGuest(
        true
    )->setCustomerEmail(
        'customer2@null.com'
    )->setBillingAddress(
        $billingAddress
    )->setShippingAddress(
        $shippingAddress
    )->setStoreId(
        $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId()
    )->addItem(
        $orderItem
    )->setPayment(
        $payment2
    );

$order->save();

$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();
