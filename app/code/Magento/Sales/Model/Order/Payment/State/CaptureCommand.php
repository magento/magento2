<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as SalesOrder;

class CaptureCommand implements CommandInterface
{
    /**
     * Run command
     *
     * @param OrderPaymentInterface $payment
     * @param string|float|int $amount
     * @param OrderInterface $order
     * @return string
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        $state = SalesOrder::STATE_PROCESSING;
        $status = false;
        $formattedAmount = $order->getBaseCurrency()->formatTxt($amount);
        if ($payment->getIsTransactionPending()) {
            $message = __(
                'An amount of %1 will be captured after being approved at the payment gateway.',
                $formattedAmount
            );
            $state = SalesOrder::STATE_PAYMENT_REVIEW;
            if ($payment->getIsFraudDetected()) {
                $status = SalesOrder::STATUS_FRAUD;
            }
        } else {
            // normal online capture: invoice is marked as "paid"
            $message = __('Captured amount of %1 online', $formattedAmount);
        }
        $this->setOrderStateAndStatus($order, $status, $state);

        return $message;
    }

    /**
     * @param SalesOrder $order
     * @param string $status
     * @param string $state
     * @return void
     */
    protected function setOrderStateAndStatus(SalesOrder $order, $status, $state)
    {
        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state)->setStatus($status);
    }
}
