<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;

class OrderViewAuthorization implements OrderViewAuthorizationInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function canView(\Magento\Sales\Model\Order $order)
    {
        $currentOrder = $this->registry->registry('current_order');
        if ($order->getId() && $order->getId() === $currentOrder->getId()) {
            return true;
        }
        return false;
    }
}
