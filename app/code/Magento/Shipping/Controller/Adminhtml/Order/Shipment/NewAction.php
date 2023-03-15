<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\ShipmentProviderInterface;

class NewAction extends Action implements HttpGetActionInterface
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
     * @param ShipmentProviderInterface $shipmentProvider
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader,
        private ?ShipmentProviderInterface $shipmentProvider = null
    ) {
        $this->shipmentProvider = $shipmentProvider ?: ObjectManager::getInstance()
            ->get(ShipmentProviderInterface::class);
        parent::__construct($context);
    }

    /**
     * Shipment create page
     *
     * @return void
     */
    public function execute()
    {
        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->shipmentProvider->getShipmentData());
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
        $shipment = $this->shipmentLoader->load();
        if ($shipment) {
            $comment = $this->_objectManager->get(BackendSession::class)->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipments'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Shipment'));
            $this->_view->renderLayout();
        } else {
            $this->_redirect('*/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }
    }
}
