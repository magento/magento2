<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Perform 'sale' payment operation.
 */
class SaleOperation
{
    /**
     * @var ProcessInvoiceOperation
     */
    private $processInvoiceOperation;

    /**
     * @param ProcessInvoiceOperation $processInvoiceOperation
     */
    public function __construct(
        ProcessInvoiceOperation $processInvoiceOperation
    ) {
        $this->processInvoiceOperation = $processInvoiceOperation;
    }

    /**
     * Authorize and Capture payment.
     *
     * @param OrderPaymentInterface $payment
     * @return OrderPaymentInterface
     * @throws LocalizedException
     */
    public function execute(OrderPaymentInterface $payment): OrderPaymentInterface
    {
        /** @var $payment Payment */
        $invoice = $payment->getOrder()->prepareInvoice();
        $invoice->register();
        $this->processInvoiceOperation->execute($payment, $invoice, 'sale');
        if ($invoice->getIsPaid()) {
            $invoice->pay();
        }
        $payment->getOrder()->addRelatedObject($invoice);
        $payment->setCreatedInvoice($invoice);
        if ($payment->getIsFraudDetected()) {
            $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
        }

        return $payment;
    }
}
