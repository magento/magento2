<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Model;

/**
 * Provide shipment items data.
 *
 * @api
 */
interface ShipmentProviderInterface
{
    /**
     * Retrieve shipment items.
     *
     * @return array
     */
    public function getShipmentData(): array;
}
