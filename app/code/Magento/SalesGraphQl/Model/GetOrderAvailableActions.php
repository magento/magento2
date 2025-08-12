<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model;

use Magento\SalesGraphQl\Api\OrderAvailableActionProviderInterface;

class GetOrderAvailableActions
{
    /**
     * @param OrderAvailableActionProviderInterface[] $actions
     */
    public function __construct(
        private readonly array $actions = []
    ) {
    }

    /**
     * Get available order action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function execute(\Magento\Sales\Model\Order $order): array
    {
        $availableAction = [];
        foreach ($this->actions as $action) {
            $availableAction[] = $action->execute($order);
        }
        return array_merge(...$availableAction);
    }
}
