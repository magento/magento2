<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Sales\Model\Order;

/**
 * Provides list of order status for the complete state
 */
class GetCompleteOrderStatusList
{
    /**
     * Provides list of order status for the complete state
     *
     * @return array
     */
    public function execute(): array
    {
        return [
            Order::STATE_COMPLETE,
            Order::STATE_CLOSED,
            Order::STATE_CANCELED
        ];
    }
}
