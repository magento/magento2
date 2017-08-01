<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Block\Adminhtml\View;

/**
 * Adminhtml sales item renderer
 *
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     * @since 2.0.0
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Shipment
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getShipment();
    }
}
