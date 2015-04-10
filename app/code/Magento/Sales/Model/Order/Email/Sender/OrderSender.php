<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Resource\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class OrderSender
 */
class OrderSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param Renderer $addressRenderer
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        Renderer $addressRenderer
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
        $this->paymentHelper = $paymentHelper;
        $this->orderResource = $orderResource;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @return bool
     */
    public function send(Order $order)
    {
        $result = $this->checkAndSend($order);
        if ($result) {
            $order->setEmailSent(true);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }
        return $result;
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $this->templateContainer->setTemplateVars(
            [
                'order' => $order,
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->addressRenderer->format($order->getShippingAddress(), 'html'),
                'formattedBillingAddress' => $this->addressRenderer->format($order->getBillingAddress(), 'html'),
            ]
        );
        parent::prepareTemplate($order);
    }

    /**
     * Get payment info block as html
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
