<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Payment::class
);
$payment->setMethod('checkmo');

$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId(
    '100000001'
)->setSubtotal(
    100
)->setBaseSubtotal(
    100
)->setCustomerIsGuest(
    true
)->setPayment(
    $payment
);

$payment->setTransactionId('trx1');
$payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

$order->save();
