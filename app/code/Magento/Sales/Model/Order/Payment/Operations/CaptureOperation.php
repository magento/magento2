<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

/**
 * Capture operation implementation.
 */
class CaptureOperation extends AbstractOperation
{
    /**
     * @var ProcessInvoiceOperation
     */
    private $processInvoiceOperation;

    /**
     * @param CommandInterface $stateCommand
     * @param BuilderInterface $transactionBuilder
     * @param ManagerInterface $transactionManager
     * @param EventManagerInterface $eventManager
     * @param ProcessInvoiceOperation $processInvoiceOperation
     */
    public function __construct(
        CommandInterface $stateCommand,
        BuilderInterface $transactionBuilder,
        ManagerInterface $transactionManager,
        EventManagerInterface $eventManager,
        ProcessInvoiceOperation $processInvoiceOperation
    ) {
        $this->processInvoiceOperation = $processInvoiceOperation;

        parent::__construct(
            $stateCommand,
            $transactionBuilder,
            $transactionManager,
            $eventManager
        );
    }

    /**
     * Captures payment.
     *
     * @param OrderPaymentInterface $payment
     * @param InvoiceInterface|null $invoice
     * @return OrderPaymentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(OrderPaymentInterface $payment, $invoice)
    {
        /**
         * @var $payment Payment
         */
        if (null === $invoice) {
            $invoice = $this->invoice($payment);
            $payment->setCreatedInvoice($invoice);
            if ($payment->getIsFraudDetected()) {
                $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            return $payment;
        }

        return $this->processInvoiceOperation->execute($payment, $invoice, 'capture');
    }
}
