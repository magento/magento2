<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Processes invoice created by SaleOperation or CaptureOperation.
 */
class ProcessInvoiceOperation extends AbstractOperation
{
    /**
     * Processes invoice and makes call to payment gateway.
     *
     * @param OrderPaymentInterface $payment
     * @param InvoiceInterface $invoice
     * @param string $operationMethod
     * @return OrderPaymentInterface
     * @throws LocalizedException
     */
    public function execute(
        OrderPaymentInterface $payment,
        InvoiceInterface $invoice,
        string $operationMethod
    ): OrderPaymentInterface {
        /**
         * @var $payment Payment
         */
        $amountToCapture = $payment->formatAmount($invoice->getBaseGrandTotal(), true);
        $order = $payment->getOrder();

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
            if ($method->canFetchTransactionInfo()) {
                $method->fetchTransactionInfo(
                    $payment,
                    $invoice->getTransactionId()
                );
            }
        }

        if ($invoice->getIsPaid()) {
            throw new LocalizedException(
                __('The transaction "%1" cannot be captured yet.', $invoice->getTransactionId())
            );
        }

        // attempt to capture: this can trigger "is_transaction_pending"
        $method = $payment->getMethodInstance();
        $method->setStore(
            $order->getStoreId()
        );

        $method->$operationMethod($payment, $amountToCapture);

        // prepare parent transaction and its amount
        $paidWorkaround = 0;
        if (!$invoice->wasPayCalled()) {
            $paidWorkaround = (double)$amountToCapture;
        }
        if ($payment->isCaptureFinal($paidWorkaround)) {
            $payment->setShouldCloseParentTransaction(true);
        }

        $transactionBuilder = $this->transactionBuilder->setPayment($payment);
        $transactionBuilder->setOrder($order);
        $transactionBuilder->setFailSafe(true);
        $transactionBuilder->setTransactionId($payment->getTransactionId());
        $transactionBuilder->setAdditionalInformation($payment->getTransactionAdditionalInfo());
        $transactionBuilder->setSalesDocument($invoice);
        $transaction = $transactionBuilder->build(Transaction::TYPE_CAPTURE);

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
}
