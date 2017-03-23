<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $billingAddress \Magento\Sales\Model\Order\Address */
$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Address::class,
    [
        'data' => [
            'firstname' => 'guest',
            'lastname' => 'guest',
            'email' => 'customer@example.com',
            'street' => 'street',
            'city' => 'Los Angeles',
            'region' => 'CA',
            'postcode' => '1',
            'country_id' => 'US',
            'telephone' => '1',
        ]
    ]
);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var $order \Magento\Sales\Model\Order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
$order->loadByIncrementId('100000001');
$clonedOrder = clone $order;
$order->setIncrementId('100000002');
$order->save();

/** @var $payment \Magento\Sales\Model\Order\Payment */
$payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Payment::class
);
$payment->setMethod('checkmo');

$order = $clonedOrder;
$order->setId(
    null
)->setCustomerEmail(
    'customer@example.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setPayment(
    $payment
);
$order->save();
