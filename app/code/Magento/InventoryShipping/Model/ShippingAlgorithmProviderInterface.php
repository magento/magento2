<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

/**
 * Returns shipping algorithm based on configuration option (extension point, SPI)
 *
 * @api
 */
interface ShippingAlgorithmProviderInterface
{
    /**
     * @return ShippingAlgorithmInterface
     */
    public function execute(): ShippingAlgorithmInterface;
}
