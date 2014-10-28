<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
