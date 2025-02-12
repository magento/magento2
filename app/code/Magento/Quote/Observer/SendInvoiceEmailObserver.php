<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;

/**
 * Class responsive for sending invoice emails when order created through storefront.
 */
class SendInvoiceEmailObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var InvoiceIdentity
     */
    private $invoiceIdentity;

    /**
     * @param LoggerInterface $logger
     * @param InvoiceSender $invoiceSender
     * @param InvoiceIdentity $invoiceIdentity
     */
    public function __construct(
        LoggerInterface $logger,
        InvoiceSender $invoiceSender,
        InvoiceIdentity $invoiceIdentity
    ) {
        $this->logger = $logger;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceIdentity = $invoiceIdentity;
    }

    /**
     * Send invoice email if allowed.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->isInvoiceEmailAllowed()) {
            return;
        }

        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();

        /**
         * a flag to set that there will be redirect to third party after confirmation
         */
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
            try {
                $invoice = current($order->getInvoiceCollection()->getItems());
                if ($invoice) {
                    $this->invoiceSender->send($invoice);
                }
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Is invoice email sending enabled
     *
     * @return bool
     */
    private function isInvoiceEmailAllowed(): bool
    {
        return $this->invoiceIdentity->isEnabled();
    }
}
