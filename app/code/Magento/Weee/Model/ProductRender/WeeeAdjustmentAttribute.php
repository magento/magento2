<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model\ProductRender;

use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterface;

/**
 * Price interface.
 * @api
 * @since 2.2.0
 */
class WeeeAdjustmentAttribute extends \Magento\Framework\Model\AbstractExtensibleModel implements
    WeeeAdjustmentAttributeInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAttributeCode($attributeCode)
    {
        $this->setData('attribute_code', $attributeCode);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAttributeCode()
    {
        return $this->getData('attribute_code');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getTaxAmount()
    {
        return $this->getData('tax_amount');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setTaxAmount($taxAmount)
    {
        $this->setData('tax_amount', $taxAmount);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAmountExclTax($amountExclTax)
    {
        $this->setData('amount_excl_tax', $amountExclTax);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAmountExclTax()
    {
        return $this->getData('amount_excl_tax');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setTaxAmountInclTax($amountInclTax)
    {
        $this->setData('tax_amount_incl_tax', $amountInclTax);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getTaxAmountInclTax()
    {
        return $this->getData('tax_amount_incl_tax');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(WeeeAdjustmentAttributeInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
