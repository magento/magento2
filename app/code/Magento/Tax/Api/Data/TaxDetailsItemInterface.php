<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Tax details items interface.
 * @api
 * @since 2.0.0
 */
interface TaxDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get code (sku or shipping code)
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get type (shipping, product, weee, gift wrapping, etc
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set type (shipping, product, weee, gift wrapping, etc
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Get tax_percent
     *
     * @return float
     * @since 2.0.0
     */
    public function getTaxPercent();

    /**
     * Set tax_percent
     *
     * @param float $taxPercent
     * @return $this
     * @since 2.0.0
     */
    public function setTaxPercent($taxPercent);

    /**
     * Get price
     *
     * @return float
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Get price including tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getPriceInclTax();

    /**
     * Set price including tax
     *
     * @param float $priceInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($priceInclTax);

    /**
     * Get row total
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowTotal();

    /**
     * Set row total
     *
     * @param float $rowTotal
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($rowTotal);

    /**
     * Get row total including tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowTotalInclTax();

    /**
     * Set row total including tax
     *
     * @param float $rowTotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($rowTotalInclTax);

    /**
     * Get row tax amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowTax();

    /**
     * Set row tax amount
     *
     * @param float $rowTax
     * @return $this
     * @since 2.0.0
     */
    public function setRowTax($rowTax);

    /**
     * Get taxable amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getTaxableAmount();

    /**
     * Set taxable amount
     *
     * @param float $taxableAmount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxableAmount($taxableAmount);

    /**
     * Get discount amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountAmount();

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get discount tax compensation amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Set discount tax compensation amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     * @since 2.0.0
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Return associated item code if this item is associated with another item, null otherwise
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getAssociatedItemCode();

    /**
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     * @since 2.0.0
     */
    public function setAssociatedItemCode($associatedItemCode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsItemExtensionInterface $extensionAttributes);
}
