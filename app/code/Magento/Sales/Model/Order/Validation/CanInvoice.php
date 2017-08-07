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
 * Class CanInvoice
 * @since 2.1.2
 */
class CanInvoice implements ValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @return array
     * @since 2.1.2
     */
    public function validate($entity)
    {
        $messages = [];

        if (!$this->isStateReadyForInvoice($entity)) {
            $messages[] = __('An invoice cannot be created when an order has a status of %1', $entity->getStatus());
        } elseif (!$this->canInvoice($entity)) {
            $messages[] = __('The order does not allow an invoice to be created.');
        }

        return $messages;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @since 2.1.2
     */
    private function isStateReadyForInvoice(OrderInterface $order)
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW ||
            $order->getState() === Order::STATE_HOLDED ||
            $order->getState() === Order::STATE_CANCELED ||
            $order->getState() === Order::STATE_COMPLETE ||
            $order->getState() === Order::STATE_CLOSED
        ) {
            return false;
        };

        return true;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @since 2.1.2
     */
    private function canInvoice(OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
    }
}
