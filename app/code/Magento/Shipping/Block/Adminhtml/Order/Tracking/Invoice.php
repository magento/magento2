<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Invoice tracking control form
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Tracking;

/**
 * @api
 * @since 100.0.2
 */
class Invoice extends \Magento\Shipping\Block\Adminhtml\Order\Tracking
{
    /**
     * Retrieve invoice
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve carriers
     *
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->_shippingConfig->getAllCarriers($this->getInvoice()->getStoreId());
    }
}
