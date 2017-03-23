<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Tracking;

use Magento\Framework\Exception\NotFoundException;

class Popup extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Shipping\Model\InfoFactory
     */
    protected $_shippingInfoFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Shipping\Model\InfoFactory $shippingInfoFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Shipping\Model\InfoFactory $shippingInfoFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
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
