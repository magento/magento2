<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

/**
 * @api
 */
interface ShipmentProviderInterface
{
    /**
     * Retrieve shipment items from request
     *
     * @return array
     */
    public function getShipment();
}
