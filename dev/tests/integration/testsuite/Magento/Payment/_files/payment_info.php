<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment as PaymentQuote;

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();

$addressData = [
    'firstname' => 'guest',
    'lastname' => 'guest',
    'email' => 'customer@example.com',
    'street' => 'street',
    'city' => 'Los Angeles',
    'region' => 'CA',
    'postcode' => '1',
    'country_id' => 'US',
    'telephone' => '1'
];
$billingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $paymentOrder */
$paymentOrder = $objectManager->create(
    Payment::class
);

$paymentOrder->setMethod(Config::METHOD_WPP_EXPRESS);
$paymentOrder->setAdditionalInformation('testing', 'testing additional data');

$amount = 100;

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setCustomerEmail('co@co.co')
    ->setIncrementId('100000001')
    ->setSubtotal($amount)
    ->setBaseSubtotal($amount)
    ->setBaseGrandTotal($amount)
    ->setGrandTotal($amount)
    ->setBaseCurrencyCode('USD')
    ->setCustomerIsGuest(true)
    ->setStoreId(1)
    ->setEmailSent(true)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setPayment($paymentOrder);
$order->save();



/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setStoreId(1)
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->setReservedOrderId('reserved_order_id');

$quote->getPayment()
    ->setMethod(Config::METHOD_WPP_EXPRESS)
    ->setAdditionalInformation('testing', 'testing additional data');

$quote->collectTotals();


/** @var CartRepositoryInterface $repository */
$repository = $objectManager->get(CartRepositoryInterface::class);
$repository->save($quote);
