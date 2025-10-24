<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
 * @since 100.1.2
 */
interface OrderRegistrarInterface
{
    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @return OrderInterface
     * @since 100.1.2
     */
    public function register(OrderInterface $order, ShipmentInterface $shipment);
}
