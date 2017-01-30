<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->load('message_order_21', 'reserved_order_id');

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->load($quote->getGiftMessageId());
$message->delete();

$quote->delete();
