<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\AbstractController;

class OrderViewAuthorization implements OrderViewAuthorizationInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->customerSession = $customerSession;
        $this->orderConfig = $orderConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function canView(\Magento\Sales\Model\Order $order)
    {
        $customerId = $this->customerSession->getCustomerId();
        $availableStatuses = $this->orderConfig->getVisibleOnFrontStatuses();
        if ($order->getId()
            && $order->getCustomerId()
            && $order->getCustomerId() == $customerId
            && in_array($order->getStatus(), $availableStatuses, true)
        ) {
            return true;
        }
        return false;
    }
}
