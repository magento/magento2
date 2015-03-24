<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * @codeCoverageIgnore
 */
class ItemDetails extends AbstractExtensibleModel implements TaxDetailsItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxPercent()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotal()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxableAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAXABLE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_CODE, $code);
    }

    /**
     * Set type (shipping, product, weee, gift wrapping, etc
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_TYPE, $type);
    }

    /**
     * Set tax_percent
     *
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_TAX_PERCENT, $taxPercent);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_PRICE, $price);
    }

    /**
     * Set price including tax
     *
     * @param float $priceInclTax
     * @return $this
     */
    public function setPriceInclTax($priceInclTax)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_PRICE_INCL_TAX, $priceInclTax);
    }

    /**
     * Set row total
     *
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_ROW_TOTAL, $rowTotal);
    }

    /**
     * Set row total including tax
     *
     * @param float $rowTotalInclTax
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_ROW_TOTAL_INCL_TAX, $rowTotalInclTax);
    }

    /**
     * Set row tax amount
     *
     * @param float $rowTax
     * @return $this
     */
    public function setRowTax($rowTax)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_ROW_TAX, $rowTax);
    }

    /**
     * Set taxable amount
     *
     * @param float $taxableAmount
     * @return $this
     */
    public function setTaxableAmount($taxableAmount)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_TAXABLE_AMOUNT, $taxableAmount);
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Set discount tax compensation amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
    {
        return $this->setData(
            TaxDetailsItemInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT,
            $discountTaxCompensationAmount
        );
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(array $appliedTaxes = null)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_APPLIED_TAXES, $appliedTaxes);
    }

    /**
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode)
    {
        return $this->setData(TaxDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE, $associatedItemCode);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface|null
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
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
