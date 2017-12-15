<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ShippingAlgorithm\ResultInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * @api
 */
interface ShippingAlgorithmInterface
{
    /**
     * @param OrderItemInterface $orderItem
     * @return ResultInterface
     */
    public function execute(OrderItemInterface $orderItem);
}
