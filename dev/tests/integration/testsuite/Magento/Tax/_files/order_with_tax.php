<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001')->setBaseToGlobalRate(2)->save();

/** @var \Magento\Tax\Model\Sales\Order\Tax $tax */
$tax = $objectManager->create('Magento\Tax\Model\Sales\Order\Tax');
$tax->setData(
    [
        'order_id' => $order->getId(),
        'code' => 'tax_code',
        'title' => 'Tax Title',
        'hidden' => 0,
        'percent' => 10,
        'priority' => 1,
        'position' => 1,
        'amount' => 10,
        'base_amount' => 10,
        'process' => 1,
        'base_real_amount' => 10,
    ]
);
$tax->save();
