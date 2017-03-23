<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Customer/_files/customer.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $customerRepository->getById(1);

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create(\Magento\GiftMessage\Model\Message::class);
$message->setSender('Romeo');
$message->setRecipient('Mercutio');
$message->setMessage('I thought all for the best.');
$message->save();

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setData(
    [
        'store_id' => 1,
        'is_active' => 1,
        'reserved_order_id' => 'message_order_21',
        'gift_message_id' => $message->getId(),
    ]
)->assignCustomer($customer);
$quote->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
