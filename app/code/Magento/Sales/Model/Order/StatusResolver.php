<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class \Magento\Sales\Model\Order\StatusResolver
 *
 * @since 2.2.0
 */
class StatusResolver
{
    /**
     * @param OrderInterface $order
     * @param string $state
     * @return string
     * @since 2.2.0
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
