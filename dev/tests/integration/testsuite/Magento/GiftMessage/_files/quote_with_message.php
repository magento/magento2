<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->setSender('Romeo');
$message->setRecipient('Mercutio');
$message->setMessage('I thought all for the best.');
$message->save();

/** @var \Magento\Sales\Model\Quote $quote */
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->setData(
    [
        'store_id' => 1,
        'is_active' => 1,
        'reserved_order_id' => 'message_order_21',
        'gift_message_id' => $message->getId(),
    ]
);
$quote->save();
