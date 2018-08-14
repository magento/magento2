<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;

$addressData = require(__DIR__ . '/address_data.php');
$objManager = Bootstrap::getObjectManager();

$billingAddress = $objManager
    ->create(Address::class, ['data' => $addressData])
    ->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping');

/** @var \Magento\Sales\Model\Order\Payment $payment */
$payment = $objManager->create(Payment::class);
$payment->setMethod(Config::METHOD_HOSTEDPRO);

/** @var \Magento\Sales\Model\Order $order */
$order = $objManager->create(Order::class);
$order->setCustomerEmail('wpphs.co@co.com')
    ->setIncrementId('100000001')
    ->setSubtotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setBaseCurrencyCode('USD')
    ->setCustomerIsGuest(true)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setPayment($payment);

$order->save();
