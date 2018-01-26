<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->setSender('Romeo');
$message->setRecipient('Mercutio');
$message->setMessage('I thought all for the best.');
$message->save();

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->setData(
    [
        'store_id' => 1,
        'is_active' => 1,
        'reserved_order_id' => 'message_order_21',
        'gift_message_id' => $message->getId(),
    ]
);
$quote->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\QuoteIdMaskFactory')
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
