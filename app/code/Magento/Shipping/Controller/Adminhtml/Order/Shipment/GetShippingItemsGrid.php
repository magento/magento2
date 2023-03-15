<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid as PackagingGrid;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

class GetShippingItemsGrid extends Action
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
     * Return grid with shipping items for Ajax request
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
        $this->shipmentLoader->load();
        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                PackagingGrid::class
            )->setIndex(
                $this->getRequest()->getParam('index')
            )->toHtml()
        );
    }
}
