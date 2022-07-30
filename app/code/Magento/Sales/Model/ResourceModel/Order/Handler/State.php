<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Payment\Model\Method\Free;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function check(Order $order)
    {
        $currentState = $order->getState();
        if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $currentState = Order::STATE_PROCESSING;
        }

        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice()) {
            if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
                && !$order->canCreditmemo()
                && !$order->canShip()
                && $order->getIsNotVirtual()
            ) {
                if ($order->getPayment()->getMethodInstance()->getCode() === Free::PAYMENT_METHOD_FREE_CODE) {
                    $this->setOrderStateAndStatus($order, Order::STATE_COMPLETE, Order::STATE_COMPLETE);
                } else {
                    $this->setOrderStateAndStatus($order, Order::STATE_CLOSED, Order::STATE_CLOSED);
                }
            } elseif ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
                $this->setOrderStateAndStatus($order, Order::STATE_COMPLETE, Order::STATE_COMPLETE);
            }
        }
        return $this;
    }

    /**
     * Set Order status
     *
     * @param Order $order
     * @param string $state
     * @param string $status
     * @return void
     */
    private function setOrderStateAndStatus(Order $order, $state, $status)
    {
        $order->setState($state)
            ->setStatus($order->getConfig()->getStateDefaultStatus($status));
    }
}
