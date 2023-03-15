<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;

class CreateLabel extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @param Action\Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param LabelGenerator $labelGenerator
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader,
        protected readonly LabelGenerator $labelGenerator
    ) {
        parent::__construct($context);
    }

    /**
     * Create shipping label action for specific shipment
     *
     * @return void
     */
    public function execute()
    {
        $response = new DataObject();
        try {
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            $this->labelGenerator->create($shipment, $this->_request);
            $shipment->save();
            $this->messageManager->addSuccess(__('You created the shipping label.'));
            $response->setOk(true);
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $response->setError(true);
            $response->setMessage(__('An error occurred while creating shipping label.'));
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
