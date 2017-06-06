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
interface FormattedPriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getFinalPrice();

    /**
     * @param string $finalPrice
     * @return void
     */
    public function setFinalPrice($finalPrice);

    /**
     * @return string
     */
    public function getMaxPrice();

    /**
     * @param string $maxPrice
     * @return void
     */
    public function setMaxPrice($maxPrice);

    /**
     * @return string
     */
    public function getMinimalPrice();

    /**
     * @param string $maxRegularPrice
     * @return void
     */
    public function setMaxRegularPrice($maxRegularPrice);

    /**
     * @return string
     */
    public function getMaxRegularPrice();

    /**
     * @param string $minRegularPrice
     * @return void
     */
    public function setMinimalRegularPrice($minRegularPrice);

    /**
     * @return string
     */
    public function getMinimalRegularPrice();

    /**
     * @param string $specialPrice
     * @return void
     */
    public function setSpecialPrice($specialPrice);

    /**
     * @return string
     */
    public function getSpecialPrice();

    /**
     * @param string $minimalPrice
     * @return void
     */
    public function setMinimalPrice($minimalPrice);

    /**
     * @return string
     */
    public function getRegularPrice();

    /**
     * @param string $regularPrice
     * @return void
     */
    public function setRegularPrice($regularPrice);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
    );
}
