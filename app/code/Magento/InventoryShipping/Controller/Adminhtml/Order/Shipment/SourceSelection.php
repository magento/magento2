<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

class SourceSelection extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @param Context $context
     * @param ShipmentLoader $shipmentLoader
     */
    public function __construct(
        Context $context,
        ShipmentLoader $shipmentLoader
    ) {
        parent::__construct($context);
        $this->shipmentLoader = $shipmentLoader;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $shipment = $this->shipmentLoader->load();
        if ($shipment) {
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Source Selection'));
            $this->_view->renderLayout();
        } else {
            $this->_redirect('*/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }
    }
}
