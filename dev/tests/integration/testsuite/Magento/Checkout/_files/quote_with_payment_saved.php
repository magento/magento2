<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'quote_with_address.php';

/** @var \Magento\Framework\Serialize\SerializerInterface $serializer */
$serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);

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
    ->setAdditionalData($serializer->serialize($paymentDetails));

$quote->collectTotals()->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
