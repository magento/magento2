<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class ItemDetails extends AbstractExtensibleModel implements TaxDetailsItemInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE                 = 'code';
    const KEY_TYPE                 = 'type';
    const KEY_TAX_PERCENT          = 'tax_percent';
    const KEY_PRICE                = 'price';
    const KEY_PRICE_INCL_TAX       = 'price_incl_tax';
    const KEY_ROW_TOTAL            = 'row_total';
    const KEY_ROW_TOTAL_INCL_TAX   = 'row_total_incl_tax';
    const KEY_ROW_TAX              = 'row_tax';
    const KEY_TAXABLE_AMOUNT       = 'taxable_amount';
    const KEY_DISCOUNT_AMOUNT      = 'discount_amount';
    const KEY_APPLIED_TAXES        = 'applied_taxes';
    const KEY_ASSOCIATED_ITEM_CODE = 'associated_item_code';
    const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTaxPercent()
    {
        return $this->getData(self::KEY_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPriceInclTax()
    {
        return $this->getData(self::KEY_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRowTotal()
    {
        return $this->getData(self::KEY_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(self::KEY_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRowTax()
    {
        return $this->getData(self::KEY_ROW_TAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTaxableAmount()
    {
        return $this->getData(self::KEY_TAXABLE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::KEY_DISCOUNT_AMOUNT);
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
    public function getAssociatedItemCode()
    {
        return $this->getData(self::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set type (shipping, product, weee, gift wrapping, etc
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set tax_percent
     *
     * @param float $taxPercent
     * @return $this
     * @since 2.0.0
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::KEY_TAX_PERCENT, $taxPercent);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set price including tax
     *
     * @param float $priceInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($priceInclTax)
    {
        return $this->setData(self::KEY_PRICE_INCL_TAX, $priceInclTax);
    }

    /**
     * Set row total
     *
     * @param float $rowTotal
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(self::KEY_ROW_TOTAL, $rowTotal);
    }

    /**
     * Set row total including tax
     *
     * @param float $rowTotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        return $this->setData(self::KEY_ROW_TOTAL_INCL_TAX, $rowTotalInclTax);
    }

    /**
     * Set row tax amount
     *
     * @param float $rowTax
     * @return $this
     * @since 2.0.0
     */
    public function setRowTax($rowTax)
    {
        return $this->setData(self::KEY_ROW_TAX, $rowTax);
    }

    /**
     * Set taxable amount
     *
     * @param float $taxableAmount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxableAmount($taxableAmount)
    {
        return $this->setData(self::KEY_TAXABLE_AMOUNT, $taxableAmount);
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Set discount tax compensation amount
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
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     * @since 2.0.0
     */
    public function setAssociatedItemCode($associatedItemCode)
    {
        return $this->setData(self::KEY_ASSOCIATED_ITEM_CODE, $associatedItemCode);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
