<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;

/**
 * Class \Magento\Quote\Model\ShippingAssignment
 *
 * @since 2.0.0
 */
class ShippingAssignment extends \Magento\Framework\Model\AbstractExtensibleModel implements ShippingAssignmentInterface
{
    const SHIPPING = 'shipping';
    const ITEMS = 'items';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getShipping()
    {
        return $this->getData(self::SHIPPING);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setShipping(\Magento\Quote\Api\Data\ShippingInterface $value)
    {
        $this->setData(self::SHIPPING, $value);
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setItems($value)
    {
        $this->setData(self::ITEMS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
