<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;

class ShippingAssignment extends AbstractExtensibleModel implements ShippingAssignmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getShipping()
    {
        return $this->_getData(ShippingAssignmentInterface::KEY_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->_getData(ShippingAssignmentInterface::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(ShippingAssignmentInterface::KEY_STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setShipping($shipping)
    {
        return $this->setData(ShippingAssignmentInterface::KEY_SHIPPING, $shipping);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items)
    {
        return $this->setData(ShippingAssignmentInterface::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId = null)
    {
        return $this->setData(ShippingAssignmentInterface::KEY_STOCK_ID, $stockId);
    }
}
