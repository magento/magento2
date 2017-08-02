<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\Operations;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

/**
 * Class AbstractOperation
 * @since 2.0.0
 */
abstract class AbstractOperation
{
    /**
     * @var CommandInterface
     * @since 2.0.0
     */
    protected $stateCommand;

    /**
     * @var BuilderInterface
     * @since 2.0.0
     */
    protected $transactionBuilder;

    /**
     * @var ManagerInterface
     * @since 2.0.0
     */
    protected $transactionManager;

    /**
     * @var EventManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param CommandInterface $stateCommand
     * @param BuilderInterface $transactionBuilder
     * @param ManagerInterface $transactionManager
     * @param EventManagerInterface $eventManager
     * @since 2.0.0
     */
    public function __construct(
        CommandInterface $stateCommand,
        BuilderInterface $transactionBuilder,
        ManagerInterface $transactionManager,
        EventManagerInterface $eventManager
    ) {
        $this->stateCommand = $stateCommand;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionManager = $transactionManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @param OrderPaymentInterface $payment
     * @return Invoice
     * @since 2.0.0
     */
    protected function invoice(OrderPaymentInterface $payment)
    {
        /** @var Invoice $invoice */
        $invoice = $payment->getOrder()->prepareInvoice();

        $invoice->register();
        if ($payment->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $payment->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }

    /**
     * Totals updater utility method
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param OrderPaymentInterface $payment
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    protected function updateTotals(OrderPaymentInterface $payment, $data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $payment->getDataUsingMethod($key);
                $payment->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Return invoice model for transaction
     *
     * @param OrderInterface $order
     * @param string $transactionId
     * @return false|Invoice
     * @since 2.0.0
     */
    protected function getInvoiceForTransactionId(OrderInterface $order, $transactionId)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId());
                // to make sure all data will properly load (maybe not required)
                return $invoice;
            }
        }
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_OPEN
                && $invoice->load($invoice->getId())
            ) {
                $invoice->setTransactionId($transactionId);
                return $invoice;
            }
        }
        return false;
    }
}
