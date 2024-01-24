<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Checking order status and adjusting order status before saving
 */
class State
{
    /**
     * Check order status and adjust the status before save
     *
     * @param Order $order
     * @return $this
     */
    public function check(Order $order)
    {
        $currentState = $order->getState();
        if ($this->canBeProcessingStatus($order, $currentState)) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $currentState = Order::STATE_PROCESSING;
        }
        if ($order->isCanceled() && $order->canUnhold() && $order->canInvoice()) {
            return $this;
        }

        if ($this->canBeClosedStatus($order, $currentState)) {
            $order->setState(Order::STATE_CLOSED)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
            return $this;
        }

        if ($this->canBeCompleteStatus($order, $currentState)) {
            $order->setState(Order::STATE_COMPLETE)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
            return $this;
        }

        return $this;
    }

    /**
     * Check if order can be automatically switched to complete status
     *
     * @param Order $order
     * @param string $currentState
     * @return bool
     */
    private function canBeCompleteStatus(Order $order, string $currentState): bool
    {
        if ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
            return true;
        }

        return false;
    }

    /**
     * Check if order can be automatically switched to closed status
     *
     * @param Order $order
     * @param string $currentState
     * @return bool
     */
    private function canBeClosedStatus(Order $order, string $currentState): bool
    {
        if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
            && !$order->canCreditmemo()
            && !$order->canShip()
            && $order->getIsNotVirtual()
        ) {
            return true;
        }

        if ($order->getIsVirtual() && $order->getStatus() === Order::STATE_CLOSED) {
            return true;
        }

        return false;
    }

    /**
     * Check if order can be automatically switched to processing status
     *
     * @param Order $order
     * @param string $currentState
     * @return bool
     */
    private function canBeProcessingStatus(Order $order, string $currentState): bool
    {
        if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
            return true;
        }

        return false;
    }
}
