<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Helper;

/**
 * Checkout workflow helper
 */
class Checkout
{
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $_session;

    /**
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session
    ) {
        $this->_session = $session;
    }

    /**
     * Cancel last placed order with specified comment message
     *
     * @param string $comment Comment appended to order history
     * @return bool True if order cancelled, false otherwise
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->_session->getLastRealOrder();
        if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }
}
