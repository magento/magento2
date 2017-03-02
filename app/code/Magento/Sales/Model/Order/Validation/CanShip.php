<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanShip
 */
class CanShip implements ValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @return array
     */
    public function validate($entity)
    {
        $messages = [];
        if (!$this->isStateReadyForShipment($entity)) {
            $messages[] = __('A shipment cannot be created when an order has a status of %1', $entity->getStatus());
        } elseif (!$this->canShip($entity)) {
            $messages[] = __('The order does not allow a shipment to be created.');
        }

        return $messages;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isStateReadyForShipment(OrderInterface $order)
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW ||
            $order->getState() === Order::STATE_HOLDED ||
            $order->getIsVirtual() ||
            $order->getState() === Order::STATE_CANCELED
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function canShip(OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() && !$item->getLockedDoShip()) {
                return true;
            }
        }

        return false;
    }
}
