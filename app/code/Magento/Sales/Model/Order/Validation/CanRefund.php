<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanRefund
 */
class CanRefund implements ValidatorInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * CanRefund constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function validate($entity)
    {
        $messages = [];
        if ($entity->getState() === Order::STATE_PAYMENT_REVIEW ||
            $entity->getState() === Order::STATE_HOLDED ||
            $entity->getState() === Order::STATE_CANCELED ||
            $entity->getState() === Order::STATE_CLOSED
        ) {
            $messages[] = __(
                'A creditmemo can not be created when an order has a status of %1',
                $entity->getStatus()
            );
        } elseif (!$this->canRefund($entity)) {
            $messages[] = __('The order does not allow a creditmemo to be created.');
        }

        /**
         * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
         * for this we have additional diapason for 0
         * TotalPaid - contains amount, that were not rounded.
         */
        if (abs($this->priceCurrency->round($entity->getTotalPaid()) - $entity->getTotalRefunded()) < .0001) {
            $messages[] = __('We can\'t create creditmemo for the order.');
        }

        return $messages;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function canRefund(OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getQtyToRefund() || $item->isDummy()) {
                return true;
            }
        }

        return false;
    }
}
