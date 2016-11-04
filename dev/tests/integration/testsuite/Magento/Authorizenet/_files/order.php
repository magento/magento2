<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$amount = 120.15;

/** @var Payment $payment */
$payment = $objectManager->get(Payment::class);
$payment
    ->setMethod('authorizenet_directpost')
    ->setAnetTransType('AUTH_ONLY')
    ->setBaseAmountAuthorized($amount)
    ->setPoNumber('10101200');

/** @var Address\ $billingAddress */
$billingAddress = $objectManager->create(Address::class, [
    'data' => [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'customer@example.com',
        'street' => 'Pearl St',
        'city' => 'Los Angeles',
        'region' => 'CA',
        'postcode' => '10020',
        'country_id' => 'US',
        'telephone' => '22-333-44',
        'address_type' => 'billing'
    ]
]);

$shippingAddress = $objectManager->create(Address::class, [
    'data' => [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'customer@example.com',
        'street' => 'Bourne St',
        'city' => 'London',
        'postcode' => 'DW23W',
        'country_id' => 'UK',
        'telephone' => '22-333-44',
        'address_type' => 'billing'
    ]
]);

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setQuoteId(2)
    ->setIncrementId('100000002')
    ->setBaseGrandTotal($amount)
    ->setBaseCurrencyCode('USD')
    ->setBaseTaxAmount($amount)
    ->setBaseShippingAmount($amount)
    ->setCustomerEmail('customer@example.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);