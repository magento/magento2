<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Catalog\Model\Product\Type;
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
     */
    public function check(Order $order)
    {
        $currentState = $order->getState();
        if ($this->checkForProcessingState($order, $currentState)) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $currentState = Order::STATE_PROCESSING;
        }
        if ($order->isCanceled() ||
            $order->canUnhold() ||
            $order->canInvoice() ||
            ($this->orderHasOpenInvoices($order) && (int) $order->getTotalDue() > 0)
        ) {
            return $this;
        }

        if ($this->checkForClosedState($order, $currentState)) {
            $order->setState(Order::STATE_CLOSED)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
            return $this;
        }

        if ($this->checkForCompleteState($order, $currentState)) {
            $order->setState(Order::STATE_COMPLETE)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
            return $this;
        }

        return $this;
    }

    /**
     * Check if order can be automatically switched to complete state
     *
     * @param Order $order
     * @param string|null $currentState
     * @return bool
     */
    private function checkForCompleteState(Order $order, ?string $currentState): bool
    {
        if ($currentState === Order::STATE_PROCESSING
            && (!$order->canShip() || $this->isPartiallyRefundedOrderShipped($order))
        ) {
            return true;
        }

        return false;
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
            && $this->getQtyItemsToShip($order) <= $this->getRefundedItems($order) + $this->getShippedItems($order)) {
            $isPartiallyRefundedOrderShipped = true;
        }

        return $isPartiallyRefundedOrderShipped;
    }

    /**
     * Get all refunded items number
     *
     * @param Order $order
     * @return int
     */
    private function getQtyItemsToShip(Order $order): int
    {
        $numOfItemsToShip = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Type::TYPE_SIMPLE) { // only simple products are accountable for the order qty
                $numOfItemsToShip += (int)$item->getQtyOrdered();
            }
        }
        return $numOfItemsToShip;
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
     * Check if order can be automatically switched to closed state
     *
     * @param Order $order
     * @param string|null $currentState
     * @return bool
     */
    private function checkForClosedState(Order $order, ?string $currentState): bool
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
     * Check if order can be automatically switched to processing state
     *
     * @param Order $order
     * @param string|null $currentState
     * @return bool
     */
    private function checkForProcessingState(Order $order, ?string $currentState): bool
    {
        if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
            return true;
        }

        return false;
    }
}
