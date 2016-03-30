<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class ShippingAssignment extends \Magento\Framework\Model\AbstractExtensibleModel implements ShippingAssignmentInterface
{
    const SHIPPING = 'shipping';
    const ITEMS = 'items';
    /**
     * @inheritDoc
     */
    public function getShipping()
    {
        return $this->getData(self::SHIPPING);
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function setShipping(\Magento\Quote\Api\Data\ShippingInterface $value)
    {
        $this->setData(self::SHIPPING, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setItems($value)
    {
        $this->setData(self::ITEMS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
