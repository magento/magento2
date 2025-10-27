<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\OrderCancellation\Model\Config\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\OrderCancellation\Model\CustomerCanCancel;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

class ValidateOrder
{
    /**
     * ValidateOrder Constructor
     *
     * @param CustomerCanCancel $customerCanCancel
     * @param OrderFormatter $orderFormatter
     * @param Config $config
     */
    public function __construct(
        private readonly CustomerCanCancel $customerCanCancel,
        private readonly OrderFormatter $orderFormatter,
        private readonly Config $config
    ) {
    }

    /**
     * Validate order cancellation
     *
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order): array
    {
        if (!$this->config->isOrderCancellationEnabledForStore((int)$order->getStoreId())) {
            return [
                'error' =>  __('Order cancellation is not enabled for requested store.')
            ];
        }

        if (!$this->customerCanCancel->execute($order)) {
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
