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
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
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
