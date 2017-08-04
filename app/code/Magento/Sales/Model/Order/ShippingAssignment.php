<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;

/**
 * Class \Magento\Sales\Model\Order\ShippingAssignment
 *
 * @since 2.0.3
 */
class ShippingAssignment extends AbstractExtensibleModel implements ShippingAssignmentInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShipping()
    {
        return $this->_getData(self::KEY_SHIPPING);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getItems()
    {
        return $this->_getData(self::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getStockId()
    {
        return $this->_getData(self::KEY_STOCK_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShipping(\Magento\Sales\Api\Data\ShippingInterface $shipping)
    {
        return $this->setData(self::KEY_SHIPPING, $shipping);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setItems(array $items)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setStockId($stockId = null)
    {
        return $this->setData(self::KEY_STOCK_ID, $stockId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
