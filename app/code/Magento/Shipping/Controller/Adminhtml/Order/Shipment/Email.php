<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\ShipmentNotifier;

/**
 * Class Email
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order\Shipment
 */
class Email extends Action
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
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader
    ) {
        parent::__construct($context);
    }

    /**
     * Send email with shipment data to customer
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        try {
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            if ($shipment) {
                $this->_objectManager->create(ShipmentNotifier::class)
                    ->notify($shipment);
                $shipment->save();
                $this->messageManager->addSuccess(__('You sent the shipment.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addError(__('Cannot send shipment information.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/view', ['shipment_id' => $this->getRequest()->getParam('shipment_id')]);
    }
}
