<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order.php';

/** @var \Magento\Sales\Model\Order $order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001');

$order->setData(
    'base_to_global_rate',
    2
)->setData(
    'base_shipping_amount',
    20
)->setData(
    'base_shipping_canceled',
    2
)->setData(
    'base_shipping_invoiced',
    20
)->setData(
    'base_shipping_refunded',
    3
)->setData(
    'is_virtual',
    0
)->save();
