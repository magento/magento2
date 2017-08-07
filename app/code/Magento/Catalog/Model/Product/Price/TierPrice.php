<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;

/**
 * TierPrice DTO.
 * @since 2.2.0
 */
class TierPrice extends \Magento\Framework\Model\AbstractExtensibleModel implements TierPriceInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setPriceType($type)
    {
        return $this->setData(self::PRICE_TYPE, $type);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPriceType()
    {
        return $this->getData(self::PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setCustomerGroup($group)
    {
        return $this->setData(self::CUSTOMER_GROUP, $group);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getCustomerGroup()
    {
        return $this->getData(self::CUSTOMER_GROUP);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
