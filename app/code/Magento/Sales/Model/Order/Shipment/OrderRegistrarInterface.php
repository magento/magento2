<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Interface OrderRegistrarInterface
 *
 * Calculate order shipped data based on created shipment
 *
 * @api
 * @since 2.1.2
 */
interface OrderRegistrarInterface
{
    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @return OrderInterface
     * @since 2.1.2
     */
    public function register(OrderInterface $order, ShipmentInterface $shipment);
}
