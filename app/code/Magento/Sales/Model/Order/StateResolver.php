<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class OrderStateResolver
 */
class StateResolver implements OrderStateResolverInterface
{
    /**
     * @param OrderInterface $order
     * @param array $arguments
     * @return string
     */
    public function getStateForOrder(OrderInterface $order, array $arguments = [])
    {
        /** @var  $order Order|OrderInterface */
        $orderState = $order->getState() === Order::STATE_PROCESSING ? Order::STATE_PROCESSING : Order::STATE_NEW;
        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {
            if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
                if ($order->getState() !== Order::STATE_COMPLETE) {
                    $orderState = Order::STATE_COMPLETE;
                }
            } elseif (floatval($order->getTotalRefunded())
                || !$order->getTotalRefunded() && in_array(self::FORCED_CREDITMEMO, $arguments)
            ) {
                if ($order->getState() !== Order::STATE_CLOSED) {
                    $orderState = Order::STATE_CLOSED;
                }
            }
        }
        if ($order->getState() == Order::STATE_NEW && in_array(self::IN_PROGRESS, $arguments)) {
            $orderState = Order::STATE_PROCESSING;
        }
        return $orderState;
    }
}
