<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Model\Resource\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Class State
 */
class State
{
    /**
     * Check order status before save
     *
     * @param Order $order
     * @return $this
     */
    public function check(Order $order)
    {
        if (!$order->getId()) {
            return $order;
        }
        $userNotification = $order->hasCustomerNoteNotify() ? $order->getCustomerNoteNotify() : null;
        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {
            if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
                if ($order->getState() !== Order::STATE_COMPLETE) {
                    $order->setState(Order::STATE_COMPLETE, true, '', $userNotification, false);
                }
            } elseif (floatval($order->getTotalRefunded())
                || !$order->getTotalRefunded() && $order->hasForcedCanCreditmemo()
            ) {
                if ($order->getState() !== Order::STATE_CLOSED) {
                    $order->setState(Order::STATE_CLOSED, true, '', $userNotification, false);
                }
            }
        }
        if ($order->getState() == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING, true, '', $userNotification);
        }
        return $this;
    }
}
