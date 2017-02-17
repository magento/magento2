<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface TotalsInterface
 * @api
 */
interface TotalsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_GRAND_TOTAL = 'grand_total';

    const KEY_BASE_GRAND_TOTAL = 'base_grand_total';

    const KEY_SUBTOTAL = 'subtotal';

    const KEY_BASE_SUBTOTAL = 'base_subtotal';

    const KEY_DISCOUNT_AMOUNT = 'discount_amount';

    const KEY_BASE_DISCOUNT_AMOUNT = 'base_discount_amount';

    const KEY_SUBTOTAL_WITH_DISCOUNT = 'subtotal_with_discount';

    const KEY_BASE_SUBTOTAL_WITH_DISCOUNT = 'base_subtotal_with_discount';

    const KEY_SHIPPING_AMOUNT = 'shipping_amount';

    const KEY_BASE_SHIPPING_AMOUNT = 'base_shipping_amount';

    const KEY_SHIPPING_DISCOUNT_AMOUNT = 'shipping_discount_amount';

    const KEY_BASE_SHIPPING_DISCOUNT_AMOUNT = 'base_shipping_discount_amount';

    const KEY_TAX_AMOUNT = 'tax_amount';

    const KEY_BASE_TAX_AMOUNT = 'base_tax_amount';

    const KEY_WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';

    const KEY_SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';

    const KEY_BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';

    const KEY_SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';

    const KEY_BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';

    const KEY_SHIPPING_INCL_TAX = 'shipping_incl_tax';

    const KEY_BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';

    const KEY_BASE_CURRENCY_CODE = 'base_currency_code';

    const KEY_QUOTE_CURRENCY_CODE = 'quote_currency_code';

    const KEY_COUPON_CODE = 'coupon_code';

    const KEY_ITEMS = 'items';

    const KEY_TOTAL_SEGMENTS = 'total_segments';

    const KEY_ITEMS_QTY = 'items_qty';

    /**#@-*/

    /**
     * Get grand total in quote currency
     *
     * @return float|null
     */
    public function getGrandTotal();

    /**
     * Set grand total in quote currency
     *
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * Get grand total in base currency
     *
     * @return float|null
     */
    public function getBaseGrandTotal();

    /**
     * Set grand total in base currency
     *
     * @param float $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal);

    /**
     * Get subtotal in quote currency
     *
     * @return float|null
     */
    public function getSubtotal();

    /**
     * Set subtotal in quote currency
     *
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal);

    /**
     * Get subtotal in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotal();

    /**
     * Set subtotal in base currency
     *
     * @param float $baseSubtotal
     * @return $this
     */
    public function setBaseSubtotal($baseSubtotal);

    /**
     * Get discount amount in quote currency
     *
     * @return float|null
     */
    public function getDiscountAmount();

    /**
     * Set discount amount in quote currency
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseDiscountAmount();

    /**
     * Set discount amount in base currency
     *
     * @param float $baseDiscountAmount
     * @return $this
     */
    public function setBaseDiscountAmount($baseDiscountAmount);

    /**
     * Get subtotal in quote currency with applied discount
     *
     * @return float|null
     */
    public function getSubtotalWithDiscount();

    /**
     * Set subtotal in quote currency with applied discount
     *
     * @param float $subtotalWithDiscount
     * @return $this
     */
    public function setSubtotalWithDiscount($subtotalWithDiscount);

    /**
     * Get subtotal in base currency with applied discount
     *
     * @return float|null
     */
    public function getBaseSubtotalWithDiscount();

    /**
     * Set subtotal in base currency with applied discount
     *
     * @param float $baseSubtotalWithDiscount
     * @return $this
     */
    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);

    /**
     * Get shipping amount in quote currency
     *
     * @return float|null
     */
    public function getShippingAmount();

    /**
     * Set shipping amount in quote currency
     *
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount);

    /**
     * Get shipping amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingAmount();

    /**
     * Set shipping amount in base currency
     *
     * @param float $baseShippingAmount
     * @return $this
     */
    public function setBaseShippingAmount($baseShippingAmount);

    /**
     * Get shipping discount amount in quote currency
     *
     * @return float|null
     */
    public function getShippingDiscountAmount();

    /**
     * Set shipping discount amount in quote currency
     *
     * @param float $shippingDiscountAmount
     * @return $this
     */
    public function setShippingDiscountAmount($shippingDiscountAmount);

    /**
     * Get shipping discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingDiscountAmount();

    /**
     * Set shipping discount amount in base currency
     *
     * @param float $baseShippingDiscountAmount
     * @return $this
     */
    public function setBaseShippingDiscountAmount($baseShippingDiscountAmount);

    /**
     * Get tax amount in quote currency
     *
     * @return float|null
     */
    public function getTaxAmount();

    /**
     * Set tax amount in quote currency
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * Get tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseTaxAmount();

    /**
     * Set tax amount in base currency
     *
     * @param float $baseTaxAmount
     * @return $this
     */
    public function setBaseTaxAmount($baseTaxAmount);

    /**
     * Returns the total weee tax applied amount in quote currency.
     *
     * @return float Item weee tax applied amount in quote currency.
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Sets the total weee tax applied amount in quote currency.
     *
     * @param float $weeeTaxAppliedAmount
     * @return $this
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount);

    /**
     * Get shipping tax amount in quote currency
     *
     * @return float|null
     */
    public function getShippingTaxAmount();

    /**
     * Set shipping tax amount in quote currency
     *
     * @param float $shippingTaxAmount
     * @return $this
     */
    public function setShippingTaxAmount($shippingTaxAmount);

    /**
     * Get shipping tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingTaxAmount();

    /**
     * Set shipping tax amount in base currency
     *
     * @param float $baseShippingTaxAmount
     * @return $this
     */
    public function setBaseShippingTaxAmount($baseShippingTaxAmount);

    /**
     * Get subtotal including tax in quote currency
     *
     * @return float|null
     */
    public function getSubtotalInclTax();

    /**
     * Set subtotal including tax in quote currency
     *
     * @param float $subtotalInclTax
     * @return $this
     */
    public function setSubtotalInclTax($subtotalInclTax);

    /**
     * Get subtotal including tax in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotalInclTax();

    /**
     * Set subtotal including tax in base currency
     *
     * @param float $baseSubtotalInclTax
     * @return $this
     */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax);

    /**
     * Get shipping including tax in quote currency
     *
     * @return float|null
     */
    public function getShippingInclTax();

    /**
     * Set shipping including tax in quote currency
     *
     * @param float $shippingInclTax
     * @return $this
     */
    public function setShippingInclTax($shippingInclTax);

    /**
     * Get shipping including tax in base currency
     *
     * @return float|null
     */
    public function getBaseShippingInclTax();

    /**
     * Set shipping including tax in base currency
     *
     * @param float $baseShippingInclTax
     * @return $this
     */
    public function setBaseShippingInclTax($baseShippingInclTax);

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode();

    /**
     * Set quote currency code
     *
     * @param string $quoteCurrencyCode
     * @return $this
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode);

    /**
     * Get applied coupon code
     *
     * @return string|null
     */
    public function getCouponCode();

    /**
     * Set applied coupon code
     *
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode($couponCode);

    /**
     * Get items qty
     *
     * @return int||null
     */
    public function getItemsQty();

    /**
     * Set items qty
     *
     * @param int $itemsQty
     * @return $this
     */
    public function setItemsQty($itemsQty = null);

    /**
     * Get totals by items
     *
     * @return \Magento\Quote\Api\Data\TotalsItemInterface[]|null
     */
    public function getItems();

    /**
     * Set totals by items
     *
     * @param \Magento\Quote\Api\Data\TotalsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * Get dynamically calculated totals
     *
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     */
    public function getTotalSegments();

    /**
     * Set dynamically calculated totals
     *
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $totals
     * @return $this
     */
    public function setTotalSegments($totals = []);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\TotalsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes);
}
