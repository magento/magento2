<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order;

require __DIR__ . '/order.php';

/** @var Order $order */
$order->setState(Order::STATE_HOLDED);
$order->setStatus(Order::STATE_HOLDED);
$order->save();
