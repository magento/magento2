<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Class \Magento\Sales\Model\Order\Shipment\OrderRegistrar
 *
 * @since 2.1.2
 */
class OrderRegistrar implements \Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface
{
    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @return OrderInterface
     * @since 2.1.2
     */
    public function register(OrderInterface $order, ShipmentInterface $shipment)
    {
        /** @var  \Magento\Sales\Api\Data\ShipmentItemInterface|\Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($shipment->getItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();
            }
        }
        return $order;
    }
}
