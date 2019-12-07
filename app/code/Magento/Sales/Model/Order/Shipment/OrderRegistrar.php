<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

class OrderRegistrar implements \Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface
{
    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @return OrderInterface
     */
    public function register(OrderInterface $order, ShipmentInterface $shipment)
    {
        $totalQty = 0;
        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($shipment->getItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();

                if (!$item->getOrderItem()->isDummy(true)) {
                    $totalQty += $item->getQty();
                }
            }
        }
        $shipment->setTotalQty($totalQty);

        return $order;
    }
}
