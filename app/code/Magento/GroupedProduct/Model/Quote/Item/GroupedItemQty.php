<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\GroupedProduct\Api\Data\GroupedItemQtyInterface;
use Magento\GroupedProduct\Api\Data\GroupedItemQtyExtensionInterface;

/**
 * Object that contains quantity information for a single associated product of a Grouped Product
 *
 * Class \Magento\GroupedProduct\Model\Quote\Item\GroupedItemQty
 */
class GroupedItemQty extends AbstractExtensibleModel implements GroupedItemQtyInterface
{
    /**
     * Set associated product id
     *
     * @param int|string $value
     *
     * @return $this
     */
    public function setProductId($value)
    {
        $this->setData(self::PRODUCT_ID, $value);

        return $this;
    }

    /**
     * Get associated product id
     *
     * @return int|string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set associated product qty
     *
     * @param int|string $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);

        return $this;
    }

    /**
     * Get associated product qty
     *
     * @return int
     */
    public function getQty()
    {
        return (int)$this->getData(self::QTY);
    }

    /**
     * Set extension attributes
     *
     * @param GroupedItemQtyExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(GroupedItemQtyExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);

        return $this;
    }

    /**
     * Get extension attributes
     *
     * @return GroupedItemQtyExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
}
