<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order')
    ->loadByIncrementId('100000001');

$order->setState(
    \Magento\Sales\Model\Order::STATE_NEW
);

$order->setStatus(
    $order->getConfig()->getStateDefaultStatus(
        \Magento\Sales\Model\Order::STATE_NEW
    )
);

$order->save();
