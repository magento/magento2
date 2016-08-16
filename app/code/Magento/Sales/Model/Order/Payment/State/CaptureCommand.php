<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as SalesOrder;

/**
 * Class CaptureCommand
 */
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
            $state = SalesOrder::STATE_PAYMENT_REVIEW;
            $message = 'An amount of %1 will be captured after being approved at the payment gateway.';
        } else {
            // normal online capture: invoice is marked as "paid"
            $message = 'Captured amount of %1 online.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = SalesOrder::STATE_PAYMENT_REVIEW;
            $status = SalesOrder::STATUS_FRAUD;
            $message .= ' Order is suspended as its capturing amount %1 is suspected to be fraudulent.';
        }
        $this->setOrderStateAndStatus($order, $status, $state);

        return __($message, $formattedAmount);
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
