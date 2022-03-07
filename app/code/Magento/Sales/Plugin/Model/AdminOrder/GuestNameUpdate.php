<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Plugin\Model\AdminOrder;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Plugin to update customer firstname, middlename and lastname after create order
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GuestNameUpdate
{
    /**
     * Quote session object
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $session;

    /**
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     */
    public function __construct(\Magento\Backend\Model\Session\Quote $quoteSession)
    {
        $this->session = $quoteSession;
    }

    /**
     * Update guest name after create order
     *
     * @param Create $subject
     * @param Order $order
     * @return Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateOrder(Create $subject, Order $order): Order
    {
        if ($this->session->getOrder()->getId()) {
            $oldOrder = $this->session->getOrder();
            if ($order->getCustomerIsGuest()) {
                $order->setCustomerFirstname($oldOrder->getCustomerFirstname());
                $order->setCustomerLastname($oldOrder->getCustomerLastname());
                if ($oldOrder->getMiddlename() === null) {
                    $order->setCustomerMiddlename($oldOrder->getCustomerMiddlename());
                }
            }
            $order->save();
        }
        return $order;
    }
}
