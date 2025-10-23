<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model;

use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Api\OrderAvailableActionProviderInterface;

class GetReorderAvailableActions implements OrderAvailableActionProviderInterface
{
    /**
     * GetReorderAvailableActions constructor
     *
     * @param Reorder $reorderHelper
     */
    public function __construct(
        private readonly Reorder $reorderHelper
    ) {
    }

    /**
     * Get reorder available action
     *
     * @param Order $order
     * @return array|string[]
     */
    public function execute(Order $order): array
    {
        return $this->reorderHelper->canReorder($order->getId()) ? ['REORDER'] : [];
    }
}
