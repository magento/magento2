<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Resource\Order\Invoice as InvoiceResource;

class InvoiceSender extends NotifySender
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
     * @param Template $templateContainer
     * @param InvoiceIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param PaymentHelper $paymentHelper
     * @param InvoiceResource $invoiceResource
     */
    public function __construct(
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
        $this->paymentHelper = $paymentHelper;
        $this->invoiceResource = $invoiceResource;
    }

    /**
     * Send email to customer
     *
     * @param Invoice $invoice
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Invoice $invoice, $notify = true, $comment = '')
    {
        $order = $invoice->getOrder();
        $this->templateContainer->setTemplateVars(
            [
                'order' => $order,
                'invoice' => $invoice,
                'comment' => $comment,
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore()
            ]
        );
        $result = $this->checkAndSend($order, $notify);
        if ($result) {
            $invoice->setEmailSent(true);
            $this->invoiceResource->saveAttribute($invoice, 'email_sent');
        }
        return $result;
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
