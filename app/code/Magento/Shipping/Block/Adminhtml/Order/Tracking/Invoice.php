<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shipment tracking control form
 *
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Tracking;

class Invoice extends \Magento\Shipping\Block\Adminhtml\Order\Tracking
{
    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve
     *
     * @return unknown
     */
    protected function _getCarriersInstances()
    {
        return $this->_shippingConfig->getAllCarriers($this->getInvoice()->getStoreId());
    }
}
