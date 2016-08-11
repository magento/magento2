<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Order Validator
 *
 */
class OrderValidator implements OrderValidatorInterface
{
    /**
     * Retrieve order invoice availability
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function canInvoice(OrderInterface $order)
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW ||
            $order->getState() === Order::STATE_HOLDED ||
            $order->getState() === Order::STATE_CANCELED ||
            $order->getState() === Order::STATE_COMPLETE ||
            $order->getState() === Order::STATE_CLOSED
        ) {
            return false;
        };
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
    }
}
