<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;

require 'order.php';

/** @var Order $order */
$order->setState(Order::STATE_HOLDED);
$order->setStatus(Order::STATE_HOLDED);
$order->save();
