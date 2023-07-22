<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Button as WidgetButton;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Model\Config as ShippingConfig;

/**
 * Shipment tracking control form
 *
 * @api
 * @since 100.0.2
 */
class Tracking extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var ShippingConfig
     */
    protected $_shippingConfig;

    /**
     * @param TemplateContext $context
     * @param ShippingConfig $shippingConfig
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        ShippingConfig $shippingConfig,
        Registry $registry,
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
            WidgetButton::class,
            ['label' => __('Add Tracking Number'), 'class' => '', 'onclick' => 'trackingControl.add()']
        );
    }

    /**
     * Retrieve shipment model instance
     *
     * @return OrderShipment
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
