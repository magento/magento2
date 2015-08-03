<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Operations;


use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class CaptureOperation extends AbstractOperation
{
    public function capture(OrderPaymentInterface $payment, $invoice)
    {
        /**
         * @var $payment Payment
         */
        if (is_null($invoice)) {
            $invoice = $this->invoice($payment);
            $payment->setCreatedInvoice($invoice);
            if ($payment->getIsFraudDetected()) {
                $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            return $payment;
        }
        $amountToCapture = $payment->formatAmount($invoice->getBaseGrandTotal());
        $order = $payment->getOrder();

        // prepare parent transaction and its amount
        $paidWorkaround = 0;
        if (!$invoice->wasPayCalled()) {
            $paidWorkaround = (double)$amountToCapture;
        }
        $payment->isCaptureFinal($paidWorkaround);

        $payment->setTransactionId(
            $this->transactionManager->generateTransactionId(
                $payment,
                Transaction::TYPE_CAPTURE,
                $payment->getAuthorizationTransaction()
            )
        );

        $this->eventManager->dispatch(
            'sales_order_payment_capture',
            ['payment' => $payment, 'invoice' => $invoice]
        );

        /**
         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
         */
        if ($invoice->getTransactionId()) {
            $method = $payment->getMethodInstance();
            $method->setStore(
                $order->getStoreId()
            );
            $method->fetchTransactionInfo(
                $payment,
                $invoice->getTransactionId()
            );
        }

        if (!$invoice->getIsPaid()) {
            // attempt to capture: this can trigger "is_transaction_pending"
            $method = $payment->getMethodInstance();
            $method->setStore(
                $order->getStoreId()
            );
            //TODO replace for sale usage
            $method->capture($payment, $amountToCapture);

            $transaction = $this->transactionBuilder->setPayment($this)
                ->setOrder($order)
                ->setFailSafe(true)
                ->setTransactionId($payment->getTransactionId())
                ->setAdditionalInformation($payment->getTransactionAdditionalInfo())
                ->setSalesDocument($invoice)->build(Transaction::TYPE_CAPTURE);
            $message = $this->stateCommand->execute($payment, $amountToCapture, $order);
            if ($payment->getIsTransactionPending()) {
                $invoice->setIsPaid(false);
            } else {
                $invoice->setIsPaid(true);
                $this->updateTotals($payment, ['base_amount_paid_online' => $amountToCapture]);
            }
            $message = $payment->prependMessage($message);
            $payment->addTransactionCommentsToOrder($transaction, $message);
            $invoice->setTransactionId($payment->getLastTransId());

            return $payment;
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('The transaction "%1" cannot be captured yet.', $invoice->getTransactionId())
        );
    }
}
