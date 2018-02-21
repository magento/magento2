<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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

$payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

$orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId(
    1
)->setProductType(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setBasePrice(
    100
)->setQtyOrdered(
    1
);

$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
$order->setCustomerEmail('mail@to.co')
    ->addItem(
    $orderItem
)->setIncrementId(
    '100000001'
)->setCustomerIsGuest(
    true
)->setStoreId(
    1
)->setEmailSent(
    1
)->setBillingAddress(
    $billingAddress
)->setPayment(
    $payment
);
$order->save();
