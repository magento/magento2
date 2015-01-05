<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Invoice;

class InvoiceCommentSender extends NotifySender
{
    /**
     * @param Template $templateContainer
     * @param InvoiceCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     */
    public function __construct(
        Template $templateContainer,
        InvoiceCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
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
                'store' => $order->getStore(),
            ]
        );
        return $this->checkAndSend($order, $notify);
    }
}
