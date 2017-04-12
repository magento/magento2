<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Invoice tracking control form
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Tracking;

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
