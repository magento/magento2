<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model;

use Magento\Sales\Model\Order;

/**
 * Check if customer can cancel an order according to its state.
 */
class CustomerCanCancel
{
    /**
     * Check if customer can cancel an order according to its state.
     *
     * Not cancellable states are: 'complete', 'on hold', 'cancel', 'closed'.
     *
     * @param Order $order
     * @return bool
     */
    public function execute(Order $order): bool
    {
        if ($order->getState() === Order::STATE_CLOSED
            || $order->getState() === Order::STATE_CANCELED
            || $order->getState() === Order::STATE_HOLDED
            || $order->getState() === Order::STATE_COMPLETE
        ) {
            return false;
        }
        return true;
    }
}
