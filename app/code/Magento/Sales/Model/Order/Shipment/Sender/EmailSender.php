<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\Order\Shipment\SenderInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Psr\Log\LoggerInterface;

/**
 * Email notification sender for Shipment.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSender extends Sender implements SenderInterface
{
    /**
     * @var Data
     */
    private $paymentHelper;

    /**
     * @var Shipment
     */
    private $shipmentResource;

    /**
     * @var ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param Template $templateContainer
     * @param ShipmentIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param Data $paymentHelper
     * @param Shipment $shipmentResource
     * @param ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        Data $paymentHelper,
        Shipment $shipmentResource,
        ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer
        );

        $this->paymentHelper = $paymentHelper;
        $this->shipmentResource = $shipmentResource;
        $this->globalConfig = $globalConfig;
        $this->eventManager = $eventManager;
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
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $forceSyncMode
     *
     * @return bool
     * @throws \Exception
     */
    public function send(
        OrderInterface $order,
        ShipmentInterface $shipment,
        ?ShipmentCommentCreationInterface $comment = null,
        $forceSyncMode = false
    ) {
        $this->identityContainer->setStore($order->getStore());
        $shipment->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'shipment' => $shipment,
                'shipment_id' => $shipment->getId(),
                'comment' => $comment ? $comment->getComment() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];
            $transportObject = new DataObject($transport);

            /**
             * Event argument `transport` is @deprecated. Use `transportObject` instead.
             */
            $this->eventManager->dispatch(
                'email_shipment_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transportObject->getData());

            if ($this->checkAndSend($order)) {
                $shipment->setEmailSent(true);

                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);

                return true;
            }
        } else {
            $shipment->setEmailSent(null);

            $this->shipmentResource->saveAttribute($shipment, 'email_sent');
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
    }

    /**
     * Returns payment info block as HTML.
     *
     * @param OrderInterface $order
     *
     * @return string
     * @throws \Exception
     */
    private function getPaymentHtml(OrderInterface $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
