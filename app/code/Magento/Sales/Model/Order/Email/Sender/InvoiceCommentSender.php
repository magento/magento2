<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class InvoiceCommentSender
 */
class InvoiceCommentSender extends NotifySender
{
    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Template $templateContainer
     * @param InvoiceCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        InvoiceCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        ManagerInterface $eventManager
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger);
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
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
        if ($order->getShippingAddress()) {
            $formattedShippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
        } else {
            $formattedShippingAddress = '';
        }
        $formattedBillingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');

        $transport = new \Magento\Framework\Object(
            ['template_vars' =>
                 [
                     'order'                    => $order,
                     'invoice'                  => $invoice,
                     'comment'                  => $comment,
                     'billing'                  => $order->getBillingAddress(),
                     'store'                    => $order->getStore(),
                     'formattedShippingAddress' => $formattedShippingAddress,
                     'formattedBillingAddress'  => $formattedBillingAddress,
                 ]
            ]
        );

        $this->eventManager->dispatch(
            'email_invoice_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport->getTemplateVars());

        return $this->checkAndSend($order, $notify);
    }
}
