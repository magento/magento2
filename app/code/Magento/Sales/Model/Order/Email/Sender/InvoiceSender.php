<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Resource\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class InvoiceSender
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var InvoiceResource
     */
    protected $invoiceResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @param Template $templateContainer
     * @param InvoiceIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param InvoiceResource $invoiceResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param Renderer $addressRenderer
     */
    public function __construct(
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        Renderer $addressRenderer
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger);
        $this->paymentHelper = $paymentHelper;
        $this->invoiceResource = $invoiceResource;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Sends order invoice email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Invoice $invoice
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $invoice->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $invoice->getOrder();

            if ($order->getShippingAddress()) {
                $formattedShippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
            } else {
                $formattedShippingAddress = '';
            }
            $formattedBillingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');
            
            $this->templateContainer->setTemplateVars(
                [
                    'order' => $order,
                    'invoice' => $invoice,
                    'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                    'billing' => $order->getBillingAddress(),
                    'payment_html' => $this->getPaymentHtml($order),
                    'store' => $order->getStore(),
                    'formattedShippingAddress' => $formattedShippingAddress,
                    'formattedBillingAddress' => $formattedBillingAddress
                ]
            );

            if ($this->checkAndSend($order)) {
                $invoice->setEmailSent(true);

                $this->invoiceResource->saveAttribute($invoice, ['send_email', 'email_sent']);

                return true;
            }
        }

        $this->invoiceResource->saveAttribute($invoice, 'send_email');

        return false;
    }

    /**
     * Return payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
