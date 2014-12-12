<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'default_rollback.php';
require __DIR__ . '/order.php';

/** @var \Magento\Sales\Model\Order $order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001');

$order->setData(
    'base_to_global_rate',
    2
)->setData(
    'base_total_refunded',
    50
)->setData(
    'base_total_online_refunded',
    40
)->setData(
    'base_total_offline_refunded',
    10
)->setData(
    'total_refunded',
    100
)->save();
