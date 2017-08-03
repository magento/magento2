<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Api\Data\ProductRender;

/**
 * List of all weee attributes, their amounts, etc.., that product has
 * @api
 * @since 2.2.0
 */
interface WeeeAdjustmentAttributeInterface
{
    /**
     * Set amount
     *
     * @param string|float $amount
     * @return void
     * @since 2.2.0
     */
    public function setAmount($amount);

    /**
     * Retrieve weee attribute amount
     *
     * @return string
     * @since 2.2.0
     */
    public function getAmount();

    /**
     * Retrieve tax which is calculated to fixed product tax attribute
     *
     * @return string
     * @since 2.2.0
     */
    public function getTaxAmount();

    /**
     * Set weee tax
     *
     * @param string $taxAmount
     * @return void
     * @since 2.2.0
     */
    public function setTaxAmount($taxAmount);

    /**
     * Set product amount without weee tax
     *
     * @param string $amountExclTax
     * @return void
     * @since 2.2.0
     */
    public function setAmountExclTax($amountExclTax);

    /**
     * Set tax amount of weee attribute
     *
     * @param string $amountInclTax
     * @return void
     * @since 2.2.0
     */
    public function setTaxAmountInclTax($amountInclTax);

    /**
     * Retrieve tax amount of weee attribute
     *
     * @return string
     * @since 2.2.0
     */
    public function getTaxAmountInclTax();

    /**
     * Retrieve product amount exclude tax
     *
     * @return string
     * @since 2.2.0
     */
    public function getAmountExclTax();

    /**
     * Set weee attribute code
     *
     * @param string $attributeCode
     * @return void
     * @since 2.2.0
     */
    public function setAttributeCode($attributeCode);

    /**
     * Retrieve weee attribute code
     *
     * @return string
     * @since 2.2.0
     */
    public function getAttributeCode();

    /**
     * @return \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface
     * @since 2.2.0
     */
    public function getExtensionAttributes();

    /**
     * @param \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
     * @return void
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
    );
}
