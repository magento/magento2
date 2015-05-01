<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface TaxDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TYPE = 'type';

    const KEY_TAX_PERCENT = 'tax_percent';

    const KEY_PRICE = 'price';

    const KEY_PRICE_INCL_TAX = 'price_incl_tax';

    const KEY_ROW_TOTAL = 'row_total';

    const KEY_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    const KEY_ROW_TAX = 'row_tax';

    const KEY_TAXABLE_AMOUNT = 'taxable_amount';

    const KEY_DISCOUNT_AMOUNT = 'discount_amount';

    const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';

    const KEY_APPLIED_TAXES = 'applied_taxes';

    const KEY_ASSOCIATED_ITEM_CODE = 'associated_item_code';
    /**#@-*/

    /**
     * Get code (sku or shipping code)
     *
     * @api
     * @return string|null
     */
    public function getCode();

    /**
     * Set code (sku or shipping code)
     *
     * @api
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get type (shipping, product, weee, gift wrapping, etc
     *
     * @api
     * @return string|null
     */
    public function getType();

    /**
     * Set type (shipping, product, weee, gift wrapping, etc
     *
     * @api
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get tax_percent
     *
     * @api
     * @return float
     */
    public function getTaxPercent();

    /**
     * Set tax_percent
     *
     * @api
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent);

    /**
     * Get price
     *
     * @api
     * @return float
     */
    public function getPrice();

    /**
     * Set price
     *
     * @api
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price including tax
     *
     * @api
     * @return float
     */
    public function getPriceInclTax();

    /**
     * Set price including tax
     *
     * @api
     * @param float $priceInclTax
     * @return $this
     */
    public function setPriceInclTax($priceInclTax);

    /**
     * Get row total
     *
     * @api
     * @return float
     */
    public function getRowTotal();

    /**
     * Set row total
     *
     * @api
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal);

    /**
     * Get row total including tax
     *
     * @api
     * @return float
     */
    public function getRowTotalInclTax();

    /**
     * Set row total including tax
     *
     * @api
     * @param float $rowTotalInclTax
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax);

    /**
     * Get row tax amount
     *
     * @api
     * @return float
     */
    public function getRowTax();

    /**
     * Set row tax amount
     *
     * @api
     * @param float $rowTax
     * @return $this
     */
    public function setRowTax($rowTax);

    /**
     * Get taxable amount
     *
     * @api
     * @return float
     */
    public function getTaxableAmount();

    /**
     * Set taxable amount

     * @api
     * @param float $taxableAmount
     * @return $this
     */
    public function setTaxableAmount($taxableAmount);

    /**
     * Get discount amount
     *
     * @api
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Set discount amount
     *
     * @api
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get discount tax compensation amount
     *
     * @api
     * @return float
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Set discount tax compensation amount
     *
     * @api
     * @param float $discountTaxCompensationAmount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

    /**
     * Get applied taxes
     *
     * @api
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @api
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Return associated item code if this item is associated with another item, null otherwise
     *
     * @api
     * @return mixed|null
     */
    public function getAssociatedItemCode();

    /**
     * Set associated item code
     *
     * @api
     * @param int $associatedItemCode
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes);
}
