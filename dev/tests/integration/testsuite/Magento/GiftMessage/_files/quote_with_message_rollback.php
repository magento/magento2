<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Quote $quote */
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->load('message_order_21', 'reserved_order_id');

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->load($quote->getGiftMessageId());
$message->delete();

$quote->delete();
