<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class EmailSender
 */
class EmailSender
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @param ManagerInterface $messageManager
     * @param Logger $logger
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        ManagerInterface $messageManager,
        Logger $logger,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender
    ) {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Send email about new order and handle mail exception
     *
     * @param Order $order
     * @return bool
     */
    public function send(Order $order)
    {
        try {
            $this->orderSender->send($order);
            $this->sendInvoiceEmail($order);
        } catch (MailException $exception) {
            $this->logger->critical($exception);
            $this->messageManager->addWarningMessage(
                __('You did not email your customer. Please check your email settings.')
            );
            return false;
        }

        return true;
    }

    /**
     * Send email about invoice paying
     *
     * @param Order $order
     */
    private function sendInvoiceEmail(Order $order): void
    {
        foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
            /** @var Invoice $invoice */
            if ($invoice->getState() === Invoice::STATE_PAID) {
                $this->invoiceSender->send($invoice);
            }
        }
    }
}
