<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'quote_with_address.php';

$quote->setReservedOrderId(
    'test_order_1_with_payment'
);

$paymentDetails = [
    'transaction_id' => 100500,
    'consumer_key'   => '123123q',
];

$quote->getPayment()
    ->setMethod('checkmo')
    ->setPoNumber('poNumber')
    ->setCcOwner('tester')
    ->setCcType('visa')
    ->setCcExpYear(2014)
    ->setCcExpMonth(1)
    ->setAdditionalData(serialize($paymentDetails));

$quote->collectTotals()->save();
