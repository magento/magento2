<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Resource\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class CreditmemoSender
 */
class CreditmemoSender extends NotifySender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var CreditmemoResource
     */
    protected $creditmemoResource;

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
     * @param CreditmemoIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param PaymentHelper $paymentHelper
     * @param CreditmemoResource $creditmemoResource
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        PaymentHelper $paymentHelper,
        CreditmemoResource $creditmemoResource,
        Renderer $addressRenderer,
        ManagerInterface $eventManager
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
        $this->paymentHelper = $paymentHelper;
        $this->creditmemoResource = $creditmemoResource;
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
    }

    /**
     * Send email to customer
     *
     * @param Creditmemo $creditmemo
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Creditmemo $creditmemo, $notify = true, $comment = '')
    {
        $order = $creditmemo->getOrder();
        if ($order->getShippingAddress()) {
            $formattedShippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
        } else {
            $formattedShippingAddress = '';
        }
        $formattedBillingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');

        $transport = new \Magento\Framework\Object(
            ['templateVars' =>
                 [
                     'order'                    => $order,
                     'creditmemo'               => $creditmemo,
                     'comment'                  => $comment,
                     'billing'                  => $order->getBillingAddress(),
                     'store'                    => $order->getStore(),
                     'formattedShippingAddress' => $formattedShippingAddress,
                     'formattedBillingAddress'  => $formattedBillingAddress,
                 ]
            ]
        );

        $this->eventManager->dispatch(
            'email_creditmemo_set_template_vars_before', array('sender' => $this, 'transport' => $transport)
        );

        $this->templateContainer->setTemplateVars($transport->getTemplateVars());

        $result = $this->checkAndSend($order, $notify);
        if ($result) {
            $creditmemo->setEmailSent(true);
            $this->creditmemoResource->saveAttribute($creditmemo, 'email_sent');
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
