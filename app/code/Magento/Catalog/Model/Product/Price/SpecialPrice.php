<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\SpecialPriceInterface;

/**
 * Product Special Price class is used to encapsulate data that can be processed by efficient price API.
 */
class SpecialPrice extends \Magento\Framework\Model\AbstractExtensibleModel implements SpecialPriceInterface
{
    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceFrom($datetime)
    {
        return $this->setData(self::PRICE_FROM, $datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceFrom()
    {
        return $this->getData(self::PRICE_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceTo($datetime)
    {
        return $this->setData(self::PRICE_TO, $datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceTo()
    {
        return $this->getData(self::PRICE_TO);
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
        \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
