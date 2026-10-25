<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model;

use Magento\OrderCancellation\Model\Config\Config;
use Magento\OrderCancellation\Model\CustomerCanCancel;
use Magento\SalesGraphQl\Api\OrderAvailableActionProviderInterface;
use Magento\Sales\Model\Order;

class GetOrderCancellationAvailableActions implements OrderAvailableActionProviderInterface
{
    /**
     * @param CustomerCanCancel $customerCanCancel
     * @param Config $config
     */
    public function __construct(
        private readonly CustomerCanCancel $customerCanCancel,
        private readonly Config $config
    ) {
    }

    /**
     * Get cancel available action
     *
     * @param Order $order
     * @return array|string[]
     */
    public function execute(Order $order): array
    {
        if ($this->config->isOrderCancellationEnabledForStore((int)$order->getStoreId())
            && $this->customerCanCancel->execute($order)
            && !$order->hasShipments()
        ) {
            return ['CANCEL'];
        }
        return [];
    }
}
