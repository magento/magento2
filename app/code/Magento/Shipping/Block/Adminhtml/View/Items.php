<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Block\Adminhtml\View;

use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment as OrderShipment;

/**
 * Adminhtml sales item renderer
 *
 * @api
 * @since 100.0.2
 */
class Items extends AbstractItems
{
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
     * Retrieve invoice order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return OrderShipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }
}
