<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class TaxDetails extends AbstractExtensibleModel implements TaxDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_SUBTOTAL      = 'subtotal';
    const KEY_TAX_AMOUNT    = 'tax_amount';
    const KEY_APPLIED_TAXES = 'applied_taxes';
    const KEY_ITEMS         = 'items';
    const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSubtotal()
    {
        return $this->getData(self::KEY_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTaxAmount()
    {
        return $this->getData(self::KEY_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(self::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAppliedTaxes()
    {
        return $this->getData(self::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return $this
     * @since 2.0.0
     */
    public function setSubtotal($subtotal)
    {
        return $this->setData(self::KEY_SUBTOTAL, $subtotal);
    }

    /**
     * Set tax amount
     *
     * @param float $taxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::KEY_TAX_AMOUNT, $taxAmount);
    }

    /**
     * Set discount amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
    {
        return $this->setData(
            self::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT,
            $discountTaxCompensationAmount
        );
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null)
    {
        return $this->setData(self::KEY_APPLIED_TAXES, $appliedTaxes);
    }

    /**
     * Set TaxDetails items
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
