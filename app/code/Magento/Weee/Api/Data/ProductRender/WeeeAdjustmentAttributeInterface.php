<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Api\Data\ProductRender;

/**
 * List of all weee attributes, their amounts, etc.., that product has
 * @api
 */
interface WeeeAdjustmentAttributeInterface
{
    /**
     * Set amount
     *
     * @param string|float $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * Retrieve weee attribute amount
     *
     * @return string
     */
    public function getAmount();

    /**
     * Retrieve tax which is calculated to fixed product tax attribute
     *
     * @return string
     */
    public function getTaxAmount();

    /**
     * Set weee tax
     *
     * @param string $taxAmount
     * @return void
     */
    public function setTaxAmount($taxAmount);

    /**
     * Set product amount without weee tax
     *
     * @param string $amountExclTax
     * @return void
     */
    public function setAmountExclTax($amountExclTax);

    /**
     * Set tax amount of weee attribute
     *
     * @param string $amountInclTax
     * @return void
     */
    public function setTaxAmountInclTax($amountInclTax);

    /**
     * Retrieve tax amount of weee attribute
     *
     * @return string
     */
    public function getTaxAmountInclTax();

    /**
     * Retrieve product amount exclude tax
     *
     * @return string
     */
    public function getAmountExclTax();

    /**
     * Set weee attribute code
     *
     * @param string $attributeCode
     * @return void
     */
    public function setAttributeCode($attributeCode);

    /**
     * Retrieve weee attribute code
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * @return \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
    );
}
