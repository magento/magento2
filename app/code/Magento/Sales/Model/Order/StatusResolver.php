<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

class StatusResolver
{
    /**
     * @param OrderInterface $order
     * @param string $state
     * @return string
     */
    public function getOrderStatusByState(OrderInterface $order, $state)
    {
        $paymentMethodOrderStatus = $order->getPayment()->getMethodInstance()
            ->getConfigData('order_status');

        return array_key_exists($paymentMethodOrderStatus, $order->getConfig()->getStateStatuses($state))
            ? $paymentMethodOrderStatus
            : $order->getConfig()->getStateDefaultStatus($state);
    }
}
