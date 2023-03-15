<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Tracking;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Model\InfoFactory;

class Popup extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var InfoFactory
     */
    protected $_shippingInfoFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param InfoFactory $shippingInfoFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        InfoFactory $shippingInfoFactory,
        OrderFactory $orderFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_shippingInfoFactory = $shippingInfoFactory;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * Popup action
     * Shows tracking info if it's present, otherwise redirects to 404
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        $shippingInfoModel = $this->_shippingInfoFactory->create()->loadByHash($this->getRequest()->getParam('hash'));
        $this->_coreRegistry->register('current_shipping_info', $shippingInfoModel);
        if (count($shippingInfoModel->getTrackingInfo()) == 0) {
            throw new NotFoundException(__('Page not found.'));
        }
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Tracking Information'));
        $this->_view->renderLayout();
    }
}
