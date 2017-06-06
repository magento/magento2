<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Api\Data\ProductRender;

/**
 * Price interface.
 * @api
 */
interface WeeeAdjustmentAttributeInterface
{
    /**
     * @param string $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * @return string
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getTaxAmount();

    /**
     * @param string $taxAmount
     * @return void
     */
    public function setTaxAmount($taxAmount);

    /**
     * @param string $amountExclTax
     * @return void
     */
    public function setAmountExclTax($amountExclTax);

    /**
     * @param string $amountInclTax
     * @return void
     */
    public function setTaxAmountInclTax($amountInclTax);

    /**
     * @return string
     */
    public function getTaxAmountInclTax();

    /**
     * @return string
     */
    public function getAmountExclTax();

    /**
     * @param string $attributeCode
     * @return void
     */
    public function setAttributeCode($attributeCode);

    /**
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
