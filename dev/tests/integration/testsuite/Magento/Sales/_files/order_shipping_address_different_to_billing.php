<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $billingAddress \Magento\Sales\Model\Order\Address */
$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Order\Address',
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
$shippingAddress->setId(null)->setPostcode('2')->setAddressType('shipping');

/** @var $order \Magento\Sales\Model\Order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001');
$clonedOrder = clone $order;

/** @var $payment \Magento\Sales\Model\Order\Payment */
$payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');
$clonedOrder->setIncrementId('100000002')
    ->setId(null)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setPayment($payment);
$clonedOrder->save();

$secondClonedOrder = clone $order;
$secondClonedOrder->setIncrementId('100000003')
    ->setId(null)
    ->setBillingAddress($billingAddress->setId(null))
    ->setShippingAddress($shippingAddress->setId(null))
    ->setPayment($payment->setId(null));
$secondClonedOrder->save();
