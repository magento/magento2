<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Api;

interface OrderAvailableActionProviderInterface
{
    /**
     * Get available order action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function execute(\Magento\Sales\Model\Order $order): array;
}
