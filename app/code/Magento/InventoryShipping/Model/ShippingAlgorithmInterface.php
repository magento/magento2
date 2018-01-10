<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Returns shipping algorithm result for the order (extension point, SPI)
 *
 * @api
 */
interface ShippingAlgorithmInterface
{
    /**
     * @param OrderInterface $order
     * @return ShippingAlgorithmResultInterface
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface;
}
