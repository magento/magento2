<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

use Magento\Sales\Api\Data\OrderItemInterface;

interface GetSkuFromOrderItemInterface
{
    /**
     * @param OrderItemInterface $orderItem
     * @return string
     */
    public function execute(OrderItemInterface $orderItem): string;
}
