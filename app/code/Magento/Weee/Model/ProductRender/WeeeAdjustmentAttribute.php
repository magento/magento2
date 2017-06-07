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
 */
class WeeeAdjustmentAttribute extends \Magento\Framework\Model\AbstractExtensibleModel implements
    WeeeAdjustmentAttributeInterface
{
    /**
     * @inheritdoc
     */
    public function setAttributeCode($attributeCode)
    {
        $this->setData('attribute_code', $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getAttributeCode()
    {
        return $this->getData('attribute_code');
    }

    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * @inheritdoc
     */
    public function getTaxAmount()
    {
        return $this->getData('tax_amount');
    }

    /**
     * @inheritdoc
     */
    public function setTaxAmount($taxAmount)
    {
        $this->setData('tax_amount', $taxAmount);
    }

    /**
     * @inheritdoc
     */
    public function setAmountExclTax($amountExclTax)
    {
        $this->setData('amount_excl_tax', $amountExclTax);
    }

    /**
     * @inheritdoc
     */
    public function getAmountExclTax()
    {
        return $this->getData('amount_excl_tax');
    }

    /**
     * @inheritdoc
     */
    public function setTaxAmountInclTax($amountInclTax)
    {
        $this->setData('tax_amount_incl_tax', $amountInclTax);
    }

    /**
     * @inheritdoc
     */
    public function getTaxAmountInclTax()
    {
        return $this->getData('tax_amount_incl_tax');
    }

    /**
     * @inheritdoc
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
     */
    public function setExtensionAttributes(
        \Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
