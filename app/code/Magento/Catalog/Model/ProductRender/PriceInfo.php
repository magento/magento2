<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;

/**
 * @inheritdoc
 * @since 2.2.0
 */
class PriceInfo extends \Magento\Framework\Model\AbstractExtensibleModel implements
    PriceInfoInterface
{
    /**
     * @return string
     * @since 2.2.0
     */
    public function getFinalPrice()
    {
        return $this->getData('final_price');
    }

    /**
     * @param string $finalPrice
     * @return void
     * @since 2.2.0
     */
    public function setFinalPrice($finalPrice)
    {
        $this->setData('final_price', $finalPrice);
    }

    /**
     * In case when we do not have max regular price - assume, that regular price are equal to final
     * and we can to retrieve final price instead
     *
     * @inheritdoc
     * @since 2.2.0
     */
    public function getMaxRegularPrice()
    {
        if (!$this->hasData('max_regular_price')) {
            return $this->getData('max_price');
        }

        return $this->getData('max_regular_price');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setMaxRegularPrice($maxRegularPrice)
    {
        $this->setData('max_regular_price', $maxRegularPrice);
    }

    /**
     * In case when we do not have min regular price - assume, that regular price are equal to final
     * and we can to retrieve final price instead
     *
     * @inheritdoc
     * @since 2.2.0
     */
    public function getMinimalRegularPrice()
    {
        if (!$this->hasData('minimal_regular_price')) {
            return $this->getData('minimal_price');
        }

        return $this->getData('minimal_regular_price');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setMinimalRegularPrice($minRegularPrice)
    {
        $this->setData('minimal_regular_price', $minRegularPrice);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->setData('special_price', $specialPrice);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getSpecialPrice()
    {
        return $this->getData('special_price');
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getMaxPrice()
    {
        return $this->getData('max_price');
    }

    /**
     * @param string $maxPrice
     * @return void
     * @since 2.2.0
     */
    public function setMaxPrice($maxPrice)
    {
        $this->setData('max_price', $maxPrice);
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getMinimalPrice()
    {
        return $this->getData('minimal_price');
    }

    /**
     * @param string $minimalPrice
     * @return void
     * @since 2.2.0
     */
    public function setMinimalPrice($minimalPrice)
    {
        $this->setData('minimal_price', $minimalPrice);
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getRegularPrice()
    {
        return $this->getData('regular_price');
    }

    /**
     * @param string $regularPrice
     * @return void
     * @since 2.2.0
     */
    public function setRegularPrice($regularPrice)
    {
        $this->setData('regular_price', $regularPrice);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getFormattedPrices()
    {
        return $this->getData('formatted_prices');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setFormattedPrices(FormattedPriceInfoInterface $formattedPriceInfo)
    {
        $this->setData('formatted_prices', $formattedPriceInfo);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
