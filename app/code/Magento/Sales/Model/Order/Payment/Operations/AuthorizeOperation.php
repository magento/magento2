<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class \Magento\Sales\Model\Order\Payment\Operations\AuthorizeOperation
 *
 * @since 2.0.0
 */
class AuthorizeOperation extends AbstractOperation
{
    /**
     * Authorizes payment.
     *
     * @param OrderPaymentInterface $payment
     * @param bool $isOnline
     * @param string|float $amount
     * @return OrderPaymentInterface
     * @since 2.0.0
     */
    public function authorize(OrderPaymentInterface $payment, $isOnline, $amount)
    {
        // check for authorization amount to be equal to grand total
        /**
         * @var $payment Payment
         */
        $payment->setShouldCloseParentTransaction(false);
        $isSameCurrency = $payment->isSameCurrency();
        if (!$isSameCurrency || !$payment->isCaptureFinal($amount)) {
            $payment->setIsFraudDetected(true);
        }

        // update totals
        $amount = $payment->formatAmount($amount, true);
        $payment->setBaseAmountAuthorized($amount);

        // do authorization
        $order = $payment->getOrder();
        if ($isOnline) {
            // invoke authorization on gateway
            $method = $payment->getMethodInstance();
            $method->setStore($order->getStoreId());
            $method->authorize($payment, $amount);
        }

        $message = $this->stateCommand->execute($payment, $amount, $order);
        // update transactions, order state and add comments
        $transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        return $payment;
    }
}
