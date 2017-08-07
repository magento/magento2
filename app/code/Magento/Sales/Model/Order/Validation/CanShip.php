<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanShip
 * @since 2.1.2
 */
class CanShip implements ValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @return array
     * @since 2.1.2
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
     * @since 2.1.2
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
     * @since 2.1.2
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
