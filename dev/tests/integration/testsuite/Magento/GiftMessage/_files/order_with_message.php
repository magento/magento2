<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../../Magento/Sales/_files/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->setSender('Romeo');
$message->setRecipient('Mercutio');
$message->setMessage('I thought all for the best.');
$message->save();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');

/** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
$orderItem = $order->getItems();
$orderItem = array_shift($orderItem);
$orderItem->setGiftMessageId($message->getId());

$order->setItems([$orderItem])->setGiftMessageId($message->getId());
$order->save();
