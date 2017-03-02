<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class Order
 */
class OrderOperation extends AbstractOperation
{
    /**
     * @param OrderPaymentInterface $payment
     * @param string|float $amount
     * @return OrderPaymentInterface
     */
    public function order(OrderPaymentInterface $payment, $amount)
    {
        /**
         * @var $payment Payment
         */
        // update totals
        $amount = $payment->formatAmount($amount, true);

        // do ordering
        $order = $payment->getOrder();

        $method = $payment->getMethodInstance();
        $method->setStore($order->getStoreId());
        $method->order($payment, $amount);

        if ($payment->getSkipOrderProcessing()) {
            return $payment;
        }

        $message = $this->stateCommand->execute($payment, $amount, $order);
        // update transactions, order state and add comments
        $transaction = $payment->addTransaction(Transaction::TYPE_ORDER);
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        return $payment;
    }
}
