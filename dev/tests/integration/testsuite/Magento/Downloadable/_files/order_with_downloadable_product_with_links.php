<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$billingAddress = $objectManager->create(
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

$payment = $objectManager->create(
    \Magento\Sales\Model\Order\Payment::class
);
$payment->setMethod('checkmo');

$orderItem = $objectManager->create(
    \Magento\Sales\Model\Order\Item::class
);
$orderItem->setProductId(
    1
)->setQtyOrdered(
    1
)->setProductType(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setProductOptions(
    [
        'links' => [1]
    ]
);

$order = $objectManager->create(
    \Magento\Sales\Model\Order::class
);

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
        0
    )->setBillingAddress(
        $billingAddress
    )->setPayment(
        $payment
    );
$order->save();
