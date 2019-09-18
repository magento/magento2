<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class responsive for sending order and invoice emails when it's created through storefront.
 */
class SubmitObserver implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Send order and invoice email.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        /**
         * a flag to set that there will be redirect to third party after confirmation
         */
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
                foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
                    if ($invoice->getState() === Invoice::STATE_PAID) {
                        $this->invoiceSender->send($invoice);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
