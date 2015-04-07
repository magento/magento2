<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Resource\Order\Shipment as ShipmentResource;

class ShipmentSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var ShipmentResource
     */
    protected $shipmentResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @param Template $templateContainer
     * @param ShipmentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param PaymentHelper $paymentHelper
     * @param ShipmentResource $shipmentResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     */
    public function __construct(
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        PaymentHelper $paymentHelper,
        ShipmentResource $shipmentResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
        $this->paymentHelper = $paymentHelper;
        $this->shipmentResource = $shipmentResource;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Sends order shipment email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Shipment $shipment
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Shipment $shipment, $forceSyncMode = false)
    {
        $shipment->setSendEmail(true);

        if (!$this->globalConfig->getValue('path/to/value/async_emails') || $forceSyncMode) {
            $this->templateContainer->setTemplateVars(
                [
                    'order' => $shipment->getOrder(),
                    'shipment' => $shipment,
                    'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
                    'billing' => $shipment->getOrder()->getBillingAddress(),
                    'payment_html' => $this->getPaymentHtml($shipment->getOrder()),
                    'store' => $shipment->getOrder()->getStore()
                ]
            );

            if ($this->checkAndSend($shipment->getOrder())) {
                $shipment->setEmailSent(true);

                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);

                return true;
            }
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
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
