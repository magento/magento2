<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Invoice tracking control form
 */
namespace Magento\Shipping\Block\Adminhtml\Order\Tracking;

use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;

/**
 * @api
 * @since 100.0.2
 */
class Invoice extends Tracking
{
    /**
     * Retrieve invoice
     *
     * @return OrderShipment
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
