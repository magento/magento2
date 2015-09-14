<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
}
