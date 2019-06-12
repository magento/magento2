<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

/**
 * Price interface.
 *
 * @api
 * @since 101.1.0
 */
interface PriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve final price
     *
     * @return float
     * @since 101.1.0
     */
    public function getFinalPrice();

    /**
     * Set the final price: usually it calculated as minimal price of the product
     *
     * Can be different depends on type of product
     *
     * @param float $finalPrice
     * @return void
     * @since 101.1.0
     */
    public function setFinalPrice($finalPrice);

    /**
     * Retrieve max price of a product
     *
     * E.g. for product with custom options is price with the most expensive custom option
     *
     * @return float
     * @since 101.1.0
     */
    public function getMaxPrice();

    /**
     * Set the max price of the product
     *
     * @param float $maxPrice
     * @return void
     * @since 101.1.0
     */
    public function setMaxPrice($maxPrice);

    /**
     * Set max regular price
     *
     * Max regular price is the same, as maximum price, except of excluding calculating special price and catalog rules
     * in it
     *
     * @param float $maxRegularPrice
     * @return void
     * @since 101.1.0
     */
    public function setMaxRegularPrice($maxRegularPrice);

    /**
     * Retrieve max regular price
     *
     * @return float
     * @since 101.1.0
     */
    public function getMaxRegularPrice();

    /**
     * The minimal regular price has the same behavior of calculation as max regular price, but is opposite price
     *
     * @param float $minRegularPrice
     * @return void
     * @since 101.1.0
     */
    public function setMinimalRegularPrice($minRegularPrice);

    /**
     * Retrieve minimal regular price
     *
     * @return float
     * @since 101.1.0
     */
    public function getMinimalRegularPrice();

    /**
     * Set special price
     *
     * Special price - is temporary price, that can be set to specific product
     *
     * @param float $specialPrice
     * @return void
     * @since 101.1.0
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Retrieve special price
     *
     * @return float
     * @since 101.1.0
     */
    public function getSpecialPrice();

    /**
     * Retrieve minimal price
     *
     * @return float
     * @since 101.1.0
     */
    public function getMinimalPrice();

    /**
     * Set minimal price
     *
     * @param float $minimalPrice
     * @return void
     * @since 101.1.0
     */
    public function setMinimalPrice($minimalPrice);

    /**
     * Retrieve regular price
     *
     * @return float
     * @since 101.1.0
     */
    public function getRegularPrice();

    /**
     * Regular price - is price of product without discounts and special price with taxes and fixed product tax
     *
     * Usually this price is corresponding to price in admin panel of product
     *
     * @param float $regularPrice
     * @return void
     * @since 101.1.0
     */
    public function setRegularPrice($regularPrice);

    /**
     * Retrieve dto with formatted prices
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterface
     * @since 101.1.0
     */
    public function getFormattedPrices();

    /**
     * Set dto with formatted prices
     *
     * @param FormattedPriceInfoInterface $formattedPriceInfo
     * @return void
     * @since 101.1.0
     */
    public function setFormattedPrices(FormattedPriceInfoInterface $formattedPriceInfo);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface|null
     * @since 101.1.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface $extensionAttributes
     * @return $this
     * @since 101.1.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface $extensionAttributes
    );
}
