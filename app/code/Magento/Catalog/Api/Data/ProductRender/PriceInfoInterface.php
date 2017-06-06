<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

/**
 * Price interface.
 * @api
 */
interface PriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return float
     */
    public function getFinalPrice();

    /**
     * @param float $finalPrice
     * @return void
     */
    public function setFinalPrice($finalPrice);

    /**
     * @return float
     */
    public function getMaxPrice();

    /**
     * @param float $maxPrice
     * @return void
     */
    public function setMaxPrice($maxPrice);

    /**
     * @param float $maxRegularPrice
     * @return void
     */
    public function setMaxRegularPrice($maxRegularPrice);

    /**
     * @return float
     */
    public function getMaxRegularPrice();

    /**
     * @param float $minRegularPrice
     * @return void
     */
    public function setMinimalRegularPrice($minRegularPrice);

    /**
     * @return float
     */
    public function getMinimalRegularPrice();

    /**
     * @param float $specialPrice
     * @return void
     */
    public function setSpecialPrice($specialPrice);

    /**
     * @return float
     */
    public function getSpecialPrice();

    /**
     * @return float
     */
    public function getMinimalPrice();

    /**
     * @param float $minimalPrice
     * @return void
     */
    public function setMinimalPrice($minimalPrice);

    /**
     * @return float
     */
    public function getRegularPrice();

    /**
     * @param float $regularPrice
     * @return void
     */
    public function setRegularPrice($regularPrice);

    /**
     * @return \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface
     */
    public function getFormattedPrices();

    /**
     * @param string[] $formattedPriceInfo
     * @return void
     */
    public function setFormattedPrices(FormattedPriceInfoInterface $formattedPriceInfo);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface $extensionAttributes
    );
}
