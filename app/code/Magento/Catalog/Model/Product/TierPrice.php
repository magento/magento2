<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class TierPrice extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductTierPriceInterface
{
    /**
     * Retrieve tier qty
     *
     * @return float
     * @since 2.0.0
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * Retrieve price value
     *
     * @return float
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * Set tier qty
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Retrieve customer group id
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * Set customer group id
     *
     * @param int $customerGroupId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
