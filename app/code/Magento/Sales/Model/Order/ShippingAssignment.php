<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        return $this->_getData(self::KEY_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->_getData(self::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(self::KEY_STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setShipping(\Magento\Sales\Api\Data\ShippingInterface $shipping)
    {
        return $this->setData(self::KEY_SHIPPING, $shipping);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId = null)
    {
        return $this->setData(self::KEY_STOCK_ID, $stockId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
