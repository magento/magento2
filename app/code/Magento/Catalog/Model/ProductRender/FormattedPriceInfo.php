<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface;

/**
 * Price interface.
 * @api
 */
class FormattedPriceInfo extends \Magento\Framework\Model\AbstractExtensibleModel implements
    FormattedPriceInfoInterface
{
    /**
     * @return string
     */
    public function getFinalPrice()
    {
        return $this->getData('final_price');
    }

    /**
     * @param float $finalPrice
     * @return void
     */
    public function setFinalPrice($finalPrice)
    {
        $this->setData('final_price', $finalPrice);
    }

    /**
     * @return string
     */
    public function getMaxPrice()
    {
        return $this->getData('max_price');
    }

    /**
     * @param string $maxPrice
     * @return void
     */
    public function setMaxPrice($maxPrice)
    {
        $this->setData('max_price', $maxPrice);
    }

    /**
     * @return string
     */
    public function getMinimalPrice()
    {
        return $this->getData('minimal_price');
    }

    /**
     * In case when we do not have max regular price - assume, that regular price are equal to final
     * and we can to retrieve final price instead
     *
     * @inheritdoc
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
     */
    public function getMinimalRegularPrice()
    {
        if (!$this->hasData('minimal_regular_price')) {
            return $this->getData('min_price');
        }

        return $this->getData('minimal_regular_price');
    }

    /**
     * @inheritdoc
     */
    public function setMinimalRegularPrice($minRegularPrice)
    {
        $this->setData('minimal_regular_price', $minRegularPrice);
    }

    /**
     * @inheritdoc
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->setData('special_price', $specialPrice);
    }

    /**
     * @inheritdoc
     */
    public function getSpecialPrice()
    {
        return $this->getData('special_price');
    }

    /**
     * @param string $minimalPrice
     * @return void
     */
    public function setMinimalPrice($minimalPrice)
    {
        $this->setData('minimal_price', $minimalPrice);
    }

    /**
     * @return string
     */
    public function getRegularPrice()
    {
        return $this->getData('regular_price');
    }

    /**
     * @param string $regularPrice
     * @return void
     */
    public function setRegularPrice($regularPrice)
    {
        $this->setData('regular_price', $regularPrice);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
