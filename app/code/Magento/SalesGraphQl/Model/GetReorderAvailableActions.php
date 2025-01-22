<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
