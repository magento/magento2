<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Quote\Model\Cart\Totals as QuoteCartTotals;

/**
 * Minimal TotalsInterface implementation for unit tests with full
 * PHPDoc and multi-line methods to satisfy static checks and styling.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TotalsInterfaceTestHelper extends QuoteCartTotals
{
    /**
     * Construct a lightweight Totals model for tests without DI.
     */
    public function __construct()
    {
        // Intentionally skip parent constructor; tests set data directly
    }

    /** @var array<string, mixed> */
    private $data = [];

    /** @var array<int, mixed> */
    private $items = [];

    /** @var array<int, mixed> */
    private $segments = [];

    /**
     * Get grand total
     *
     * @return float|int|null
     */
    public function getGrandTotal()
    {
        return $this->data['grand_total'] ?? null;
    }

    /**
     * Set grand total
     *
     * @param float|int $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal)
    {
        $this->data['grand_total'] = $grandTotal;
        return $this;
    }

    /**
     * Get base grand total
     *
     * @return float|int|null
     */
    public function getBaseGrandTotal()
    {
        return $this->data['base_grand_total'] ?? null;
    }

    /**
     * Set base grand total
     *
     * @param float|int $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        $this->data['base_grand_total'] = $baseGrandTotal;
        return $this;
    }

    /**
     * Get subtotal
     *
     * @return float|int|null
     */
    public function getSubtotal()
    {
        return $this->data['subtotal'] ?? null;
    }

    /**
     * Set subtotal
     *
     * @param float|int $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->data['subtotal'] = $subtotal;
        return $this;
    }

    /**
     * Get base subtotal
     *
     * @return float|int|null
     */
    public function getBaseSubtotal()
    {
        return $this->data['base_subtotal'] ?? null;
    }

    /**
     * Set base subtotal
     *
     * @param float|int $baseSubtotal
     * @return $this
     */
    public function setBaseSubtotal($baseSubtotal)
    {
        $this->data['base_subtotal'] = $baseSubtotal;
        return $this;
    }

    /**
     * Get discount amount
     *
     * @return float|int|null
     */
    public function getDiscountAmount()
    {
        return $this->data['discount_amount'] ?? null;
    }

    /**
     * Set discount amount
     *
     * @param float|int $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->data['discount_amount'] = $discountAmount;
        return $this;
    }

    /**
     * Get base discount amount
     *
     * @return float|int|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->data['base_discount_amount'] ?? null;
    }

    /**
     * Set base discount amount
     *
     * @param float|int $baseDiscountAmount
     * @return $this
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        $this->data['base_discount_amount'] = $baseDiscountAmount;
        return $this;
    }

    /**
     * Get subtotal with discount
     *
     * @return float|int|null
     */
    public function getSubtotalWithDiscount()
    {
        return $this->data['subtotal_with_discount'] ?? null;
    }

    /**
     * Set subtotal with discount
     *
     * @param float|int $subtotalWithDiscount
     * @return $this
     */
    public function setSubtotalWithDiscount($subtotalWithDiscount)
    {
        $this->data['subtotal_with_discount'] = $subtotalWithDiscount;
        return $this;
    }

    /**
     * Get base subtotal with discount
     *
     * @return float|int|null
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->data['base_subtotal_with_discount'] ?? null;
    }

    /**
     * Set base subtotal with discount
     *
     * @param float|int $baseSubtotalWithDiscount
     * @return $this
     */
    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount)
    {
        $this->data['base_subtotal_with_discount'] = $baseSubtotalWithDiscount;
        return $this;
    }

    /**
     * Get shipping amount
     *
     * @return float|int|null
     */
    public function getShippingAmount()
    {
        return $this->data['shipping_amount'] ?? null;
    }

    /**
     * Set shipping amount
     *
     * @param float|int $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->data['shipping_amount'] = $shippingAmount;
        return $this;
    }

    /**
     * Get base shipping amount
     *
     * @return float|int|null
     */
    public function getBaseShippingAmount()
    {
        return $this->data['base_shipping_amount'] ?? null;
    }

    /**
     * Set base shipping amount
     *
     * @param float|int $baseShippingAmount
     * @return $this
     */
    public function setBaseShippingAmount($baseShippingAmount)
    {
        $this->data['base_shipping_amount'] = $baseShippingAmount;
        return $this;
    }

    /**
     * Get shipping discount amount
     *
     * @return float|int|null
     */
    public function getShippingDiscountAmount()
    {
        return $this->data['shipping_discount_amount'] ?? null;
    }

    /**
     * Set shipping discount amount
     *
     * @param float|int $shippingDiscountAmount
     * @return $this
     */
    public function setShippingDiscountAmount($shippingDiscountAmount)
    {
        $this->data['shipping_discount_amount'] = $shippingDiscountAmount;
        return $this;
    }

    /**
     * Get base shipping discount amount
     *
     * @return float|int|null
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->data['base_shipping_discount_amount'] ?? null;
    }

    /**
     * Set base shipping discount amount
     *
     * @param float|int $baseShippingDiscountAmount
     * @return $this
     */
    public function setBaseShippingDiscountAmount($baseShippingDiscountAmount)
    {
        $this->data['base_shipping_discount_amount'] = $baseShippingDiscountAmount;
        return $this;
    }

    /**
     * Get tax amount
     *
     * @return float|int|null
     */
    public function getTaxAmount()
    {
        return $this->data['tax_amount'] ?? null;
    }

    /**
     * Set tax amount
     *
     * @param float|int $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->data['tax_amount'] = $taxAmount;
        return $this;
    }

    /**
     * Get base tax amount
     *
     * @return float|int|null
     */
    public function getBaseTaxAmount()
    {
        return $this->data['base_tax_amount'] ?? null;
    }

    /**
     * Set base tax amount
     *
     * @param float|int $baseTaxAmount
     * @return $this
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        $this->data['base_tax_amount'] = $baseTaxAmount;
        return $this;
    }

    /**
     * Get WEEE tax applied amount
     *
     * @return float|int|null
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->data['weee_tax_applied_amount'] ?? null;
    }

    /**
     * Set WEEE tax applied amount
     *
     * @param float|int $weeeTaxAppliedAmount
     * @return $this
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount)
    {
        $this->data['weee_tax_applied_amount'] = $weeeTaxAppliedAmount;
        return $this;
    }

    /**
     * Get shipping tax amount
     *
     * @return float|int|null
     */
    public function getShippingTaxAmount()
    {
        return $this->data['shipping_tax_amount'] ?? null;
    }

    /**
     * Set shipping tax amount
     *
     * @param float|int $shippingTaxAmount
     * @return $this
     */
    public function setShippingTaxAmount($shippingTaxAmount)
    {
        $this->data['shipping_tax_amount'] = $shippingTaxAmount;
        return $this;
    }

    /**
     * Get base shipping tax amount
     *
     * @return float|int|null
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->data['base_shipping_tax_amount'] ?? null;
    }

    /**
     * Set base shipping tax amount
     *
     * @param float|int $baseShippingTaxAmount
     * @return $this
     */
    public function setBaseShippingTaxAmount($baseShippingTaxAmount)
    {
        $this->data['base_shipping_tax_amount'] = $baseShippingTaxAmount;
        return $this;
    }

    /**
     * Get subtotal including tax
     *
     * @return float|int|null
     */
    public function getSubtotalInclTax()
    {
        return $this->data['subtotal_incl_tax'] ?? null;
    }

    /**
     * Set subtotal including tax
     *
     * @param float|int $subtotalInclTax
     * @return $this
     */
    public function setSubtotalInclTax($subtotalInclTax)
    {
        $this->data['subtotal_incl_tax'] = $subtotalInclTax;
        return $this;
    }

    /**
     * Get base subtotal including tax
     *
     * @return float|int|null
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->data['base_subtotal_incl_tax'] ?? null;
    }

    /**
     * Set base subtotal including tax
     *
     * @param float|int $baseSubtotalInclTax
     * @return $this
     */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax)
    {
        $this->data['base_subtotal_incl_tax'] = $baseSubtotalInclTax;
        return $this;
    }

    /**
     * Get shipping including tax
     *
     * @return float|int|null
     */
    public function getShippingInclTax()
    {
        return $this->data['shipping_incl_tax'] ?? null;
    }

    /**
     * Set shipping including tax
     *
     * @param float|int $shippingInclTax
     * @return $this
     */
    public function setShippingInclTax($shippingInclTax)
    {
        $this->data['shipping_incl_tax'] = $shippingInclTax;
        return $this;
    }

    /**
     * Get base shipping including tax
     *
     * @return float|int|null
     */
    public function getBaseShippingInclTax()
    {
        return $this->data['base_shipping_incl_tax'] ?? null;
    }

    /**
     * Set base shipping including tax
     *
     * @param float|int $baseShippingInclTax
     * @return $this
     */
    public function setBaseShippingInclTax($baseShippingInclTax)
    {
        $this->data['base_shipping_incl_tax'] = $baseShippingInclTax;
        return $this;
    }

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        return $this->data['base_currency_code'] ?? null;
    }

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        $this->data['base_currency_code'] = $baseCurrencyCode;
        return $this;
    }

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode()
    {
        return $this->data['quote_currency_code'] ?? null;
    }

    /**
     * Set quote currency code
     *
     * @param string $quoteCurrencyCode
     * @return $this
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode)
    {
        $this->data['quote_currency_code'] = $quoteCurrencyCode;
        return $this;
    }

    /**
     * Get coupon code
     *
     * @return string|null
     */
    public function getCouponCode()
    {
        return $this->data['coupon_code'] ?? null;
    }

    /**
     * Set coupon code
     *
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode($couponCode)
    {
        $this->data['coupon_code'] = $couponCode;
        return $this;
    }

    /**
     * Get items quantity
     *
     * @return float|int|null
     */
    public function getItemsQty()
    {
        return $this->data['items_qty'] ?? null;
    }

    /**
     * Set items quantity
     *
     * @param float|int|null $itemsQty
     * @return $this
     */
    public function setItemsQty($itemsQty = null)
    {
        $this->data['items_qty'] = $itemsQty;
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems(?array $items = null)
    {
        $this->items = $items ?? [];
        return $this;
    }

    public function getTotalSegments()
    {
        return $this->segments;
    }

    public function setTotalSegments($totals = [])
    {
        $this->segments = $totals;
        return $this;
    }

    public function getExtensionAttributes()
    {
        return null;
    }

    /**
     * Set extension attributes
     *
     * @param \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes)
    {
        $this->data['extension_attributes'] = $extensionAttributes;
        return $this;
    }
}
