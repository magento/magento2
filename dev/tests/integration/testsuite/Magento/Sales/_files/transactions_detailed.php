<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Sales\Model\Order\Payment $payment */
$payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId('100000006')->setSubtotal(100)->setBaseSubtotal(100)->setCustomerIsGuest(true)
    ->setPayment($payment);

$payment->setTransactionId('trx_auth');
$payment->setIsTransactionClosed(true);
$payment->setTransactionAdditionalInfo('auth_key', 'data');
$payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

$payment->resetTransactionAdditionalInfo();

$payment->setTransactionId('trx_capture');
$payment->setIsTransactionClosed(false);
$payment->setTransactionAdditionalInfo('capture_key', 'data');
$payment->setParentTransactionId('trx_auth');
$payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

$order->save();
