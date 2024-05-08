<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

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

        if (!$order->isCanceled()
            && !$order->canUnhold()
            && !$order->canInvoice()
            && (!$this->orderHasOpenInvoices($order) || (int) $order->getTotalDue() == 0)
        ) {
            if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
                && !$order->canCreditmemo()
                && !$order->canShip()
                && $order->getIsNotVirtual()
            ) {
                $order->setState(Order::STATE_CLOSED)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
            } elseif ($currentState === Order::STATE_PROCESSING
                && (!$order->canShip() || $this->isPartiallyRefundedOrderShipped($order))
            ) {
                $order->setState(Order::STATE_COMPLETE)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
            } elseif ($order->getIsVirtual() && $order->getStatus() === Order::STATE_CLOSED) {
                $order->setState(Order::STATE_CLOSED);
            }
        }
        return $this;
    }

    /**
     * Check if all items are remaining items after partially refunded are shipped
     *
     * @param Order $order
     * @return bool
     */
    public function isPartiallyRefundedOrderShipped(Order $order): bool
    {
        $isPartiallyRefundedOrderShipped = false;
        if ($this->getShippedItems($order) > 0
            && $order->getTotalQtyOrdered() <= $this->getRefundedItems($order) + $this->getShippedItems($order)) {
            $isPartiallyRefundedOrderShipped = true;
        }

        return $isPartiallyRefundedOrderShipped;
    }

    /**
     * Check if order has unpaid invoices
     *
     * @param Order $order
     * @return bool
     */
    private function orderHasOpenInvoices(Order $order): bool
    {
        /** @var Invoice $invoice */
        foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
            if ($invoice->getState() == Invoice::STATE_OPEN) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all refunded items number
     *
     * @param Order $order
     * @return int
     */
    private function getRefundedItems(Order $order): int
    {
        $numOfRefundedItems = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $numOfRefundedItems += (int)$item->getQtyRefunded();
            }
        }
        return $numOfRefundedItems;
    }

    /**
     * Get all shipped items number
     *
     * @param Order $order
     * @return int
     */
    private function getShippedItems(Order $order): int
    {
        $numOfShippedItems = 0;
        foreach ($order->getAllItems() as $item) {
            $numOfShippedItems += (int)$item->getQtyShipped();
        }
        return $numOfShippedItems;
    }
}
