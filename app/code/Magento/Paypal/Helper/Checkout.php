<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Helper;

use Magento\Sales\Model\Order;

/**
 * Checkout workflow helper
 * @since 2.0.0
 */
class Checkout
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $session;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session
    ) {
        $this->session = $session;
    }

    /**
     * Cancel last placed order with specified comment message
     *
     * @param string $comment Comment appended to order history
     * @return bool True if order cancelled, false otherwise
     * @since 2.0.0
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->session->getLastRealOrder();
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * Restores quote
     *
     * @return bool
     * @since 2.0.0
     */
    public function restoreQuote()
    {
        return $this->session->restoreQuote();
    }
}
