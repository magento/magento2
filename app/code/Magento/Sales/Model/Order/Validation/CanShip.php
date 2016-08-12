<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanShip
 */
class CanShip implements ValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @return string[]
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     */
    public function validate($entity)
    {
        $messages = [];
        if (!$this->canShip($entity)) {
            $messages[] = __('The order does not allow an shipment to be created.');
        }

        return $messages;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function canShip(OrderInterface $order)
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW ||
            $order->getState() === Order::STATE_HOLDED ||
            $order->getIsVirtual() ||
            $order->getState() === Order::STATE_CANCELED
        ) {
            return false;
        }

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() && !$item->getLockedDoShip()) {
                return true;
            }
        }

        return false;
    }
}
