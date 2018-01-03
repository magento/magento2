<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * @api
 */
interface ShippingAlgorithmInterface
{
    /**
     * Returns shipping algorithm result for the order
     *
     * @param OrderInterface $order
     * @return ShippingAlgorithmResultInterface
     */
    public function get(OrderInterface $order): ShippingAlgorithmResultInterface;
}
