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

namespace Magento\SalesGraphQl\Model;

use Magento\SalesGraphQl\Api\OrderAvailableActionProviderInterface;

class GetReorderAvailableActions implements OrderAvailableActionProviderInterface
{
    /**
     * Get reorder available action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array|string[]
     */
    public function execute(\Magento\Sales\Model\Order $order): array
    {
        if ($order->canReorder()) {
            return ['REORDER'];
        }
        return [];
    }
}
