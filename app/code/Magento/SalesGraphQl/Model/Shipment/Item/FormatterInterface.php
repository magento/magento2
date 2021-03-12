<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Shipment\Item;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

/**
 * Format shipment items for GraphQl output
 * @api
 */
interface FormatterInterface
{
    /**
     * Format a shipment item for GraphQl
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentItemInterface $item
     * @return array|null
     */
    public function formatShipmentItem(ShipmentInterface $shipment, ShipmentItemInterface $item): ?array;
}
