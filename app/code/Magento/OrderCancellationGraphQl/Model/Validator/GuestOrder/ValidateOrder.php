<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator\GuestOrder;

use Magento\Framework\Exception\LocalizedException;
use Magento\OrderCancellation\Model\Config\Config;
use Magento\OrderCancellation\Model\CustomerCanCancel as CanCancelOrder;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

/**
 * Ensure all conditions to cancel guest order are met
 */
class ValidateOrder
{
    /**
     * ValidateOrder Constructor
     *
     * @param OrderFormatter $orderFormatter
     * @param Config $config
     * @param CanCancelOrder $canCancelOrder
     */
    public function __construct(
        private readonly OrderFormatter $orderFormatter,
        private readonly Config $config,
        private readonly CanCancelOrder $canCancelOrder,
    ) {
    }

    /**
     * Ensure the order is valid for cancellation and returns error if any
     *
     * @param Order $order
     * @return array
     * @throws LocalizedException
     */
    public function execute(Order $order): array
    {
        if (!$this->config->isOrderCancellationEnabledForStore((int)$order->getStoreId())) {
            return [
                'error' =>  __('Order cancellation is not enabled for requested store.')
            ];
        }

        if (!$order->getCustomerIsGuest()) {
            return [
                'error' => __('Current user is not authorized to cancel this order')
            ];
        }

        if (!$this->canCancelOrder->execute($order)) {
            return [
                'error' => __('Order already closed, complete, cancelled or on hold'),
                'order' => $this->orderFormatter->format($order)
            ];
        }

        if ($order->hasShipments()) {
            return [
                'error' => __('Order with one or more items shipped cannot be cancelled'),
                'order' => $this->orderFormatter->format($order)
            ];
        }

        return [];
    }
}
