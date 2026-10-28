<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Model;

/**
 * Provide shipment items data.
 *
 * @api
 * @since 100.3.0
 */
interface ShipmentProviderInterface
{
    /**
     * Retrieve shipment items.
     *
     * @return array
     * @since 100.3.0
     */
    public function getShipmentData(): array;
}
