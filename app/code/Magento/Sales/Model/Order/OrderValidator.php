<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

/**
 * Order Validator
 *
 */
class OrderValidator implements OrderValidatorInterface
{
    /**
     * @var OrderItemValidatorInterface
     */
    private $orderItemValidator;

    /**
     * OrderValidator constructor.
     * @param OrderItemValidatorInterface $orderItemValidator
     */
    public function __construct(OrderItemValidatorInterface $orderItemValidator)
    {
        $this->orderItemValidator = $orderItemValidator;
    }

    /**
     * Retrieve order invoice availability
     *
     * @param OrderInterface $order
     * @return array
     */
    public function canInvoice(OrderInterface $order)
    {
        if ($order->getState() === Order::STATE_PAYMENT_REVIEW ||
            $order->getState() === Order::STATE_HOLDED ||
            $order->getState() === Order::STATE_CANCELED ||
            $order->getState() === Order::STATE_COMPLETE ||
            $order->getState() === Order::STATE_CLOSED
        ) {
            return false;
        };
        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if ($this->orderItemValidator->canInvoice($item) && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
    }
}
