<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order.php';

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->load('100000001', 'increment_id');
$order->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
)->setStoreId(
    $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore('default')->getId()
);
$order->save();
