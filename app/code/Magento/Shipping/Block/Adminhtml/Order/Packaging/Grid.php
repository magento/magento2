<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Packaging;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Store\Model\ScopeInterface;

class Grid extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Shipping::order/packaging/grid.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var ItemFactory
     */
    protected $_shipmentItemFactory;

    /**
     * @param TemplateContext $context
     * @param ItemFactory $shipmentItemFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        ItemFactory $shipmentItemFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return collection of shipment items
     *
     * @return array
     */
    public function getCollection()
    {
        if ($this->getShipment()->getId()) {
            $collection = $this->_shipmentItemFactory->create()->getCollection()->setShipmentFilter(
                $this->getShipment()->getId()
            );
        } else {
            $collection = $this->getShipment()->getAllItems();
        }
        return $collection;
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
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        $storeId = $this->getShipment()->getStoreId();
        $order = $this->getShipment()->getOrder();
        $address = $order->getShippingAddress();
        $shipperAddressCountryCode = $this->_scopeConfig->getValue(
            OrderShipment::XML_PATH_STORE_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $recipientAddressCountryCode = $address->getCountryId();
        if ($shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Format price
     *
     * @param   float $value
     * @return  string
     */
    public function formatPrice($value)
    {
        return sprintf('%.2F', $value);
    }
}
