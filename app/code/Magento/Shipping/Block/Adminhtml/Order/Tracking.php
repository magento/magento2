<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order;

/**
 * Shipment tracking control form
 *
 */
class Tracking extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_shippingConfig = $shippingConfig;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Prepares layout of block
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Add Tracking Number'), 'class' => '', 'onclick' => 'trackingControl.add()']
        );
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->_shippingConfig->getAllCarriers($this->getShipment()->getStoreId());
    }
}
