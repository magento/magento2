<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\SpecialPriceInterface;

/**
 * Product Special Price class is used to encapsulate data that can be processed by efficient price API.
 * @since 2.2.0
 */
class SpecialPrice extends \Magento\Framework\Model\AbstractExtensibleModel implements SpecialPriceInterface
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
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
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
    public function setPriceFrom($datetime)
    {
        return $this->setData(self::PRICE_FROM, $datetime);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPriceFrom()
    {
        return $this->getData(self::PRICE_FROM);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setPriceTo($datetime)
    {
        return $this->setData(self::PRICE_TO, $datetime);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPriceTo()
    {
        return $this->getData(self::PRICE_TO);
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
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
