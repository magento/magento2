<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;

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
            && (!$order->canShip() || $this->areAllItemsFulfilled($order))
        ) {
            return true;
        }

        return false;
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
            && (!$order->canShip() || $this->areAllItemsFulfilled($order))
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
     * Determine whether all shippable items have been fulfilled by shipment, refund, or cancellation.
     *
     * @param Order $order
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function areAllItemsFulfilled(Order $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                continue;
            }

            // For bundle shipped together, evaluate fulfillment using the parent only
            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getProductType() === Type::TYPE_BUNDLE) {
                $parentProduct = $parentItem->getProduct();
                if ($parentProduct && $parentProduct->getShipmentType() == AbstractType::SHIPMENT_TOGETHER) {
                    continue;
                }
            }

            $subject = $parentItem && $parentItem->getProductType() === Type::TYPE_BUNDLE
            && $parentItem->getProduct()
            && $parentItem->getProduct()->getShipmentType() == AbstractType::SHIPMENT_TOGETHER
                ? $parentItem
                : $item;

            $qtyOrdered = (int) $subject->getQtyOrdered();
            $qtyCanceled = (int) $subject->getQtyCanceled();
            $qtyShipped  = (int) $subject->getQtyShipped();
            $qtyRefunded = (int) $subject->getQtyRefunded();

            $openQty = $qtyOrdered - $qtyCanceled - $qtyShipped - $qtyRefunded;
            if ($openQty > 0) {
                return false;
            }
        }

        return true;
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
