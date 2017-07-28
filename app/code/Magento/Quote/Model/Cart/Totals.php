<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Cart Totals
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Totals extends AbstractExtensibleModel implements TotalsInterface
{
    /**
     * Get grand total in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGrandTotal()
    {
        return $this->getData(self::KEY_GRAND_TOTAL);
    }

    /**
     * Set grand total in quote currency
     *
     * @param float $grandTotal
     * @return $this
     * @since 2.0.0
     */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::KEY_GRAND_TOTAL, $grandTotal);
    }

    /**
     * Get grand total in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(self::KEY_BASE_GRAND_TOTAL);
    }

    /**
     * Set grand total in base currency
     *
     * @param float $baseGrandTotal
     * @return $this
     * @since 2.0.0
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        return $this->setData(self::KEY_BASE_GRAND_TOTAL, $baseGrandTotal);
    }

    /**
     * Get subtotal in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getSubtotal()
    {
        return $this->getData(self::KEY_SUBTOTAL);
    }

    /**
     * Set subtotal in quote currency
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
     * Get subtotal in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseSubtotal()
    {
        return $this->getData(self::KEY_BASE_SUBTOTAL);
    }

    /**
     * Set subtotal in base currency
     *
     * @param float $baseSubtotal
     * @return $this
     * @since 2.0.0
     */
    public function setBaseSubtotal($baseSubtotal)
    {
        return $this->setData(self::KEY_BASE_SUBTOTAL, $baseSubtotal);
    }

    /**
     * Get discount amount in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * Set discount amount in quote currency
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
     * Get discount amount in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(self::KEY_BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Set discount amount in base currency
     *
     * @param float $baseDiscountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        return $this->setData(self::KEY_BASE_DISCOUNT_AMOUNT, $baseDiscountAmount);
    }

    /**
     * Get subtotal in quote currency with applied discount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getData(self::KEY_SUBTOTAL_WITH_DISCOUNT);
    }

    /**
     * Set subtotal in quote currency with applied discount
     *
     * @param float $subtotalWithDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setSubtotalWithDiscount($subtotalWithDiscount)
    {
        return $this->setData(self::KEY_SUBTOTAL_WITH_DISCOUNT, $subtotalWithDiscount);
    }

    /**
     * Get subtotal in base currency with applied discount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getData(self::KEY_BASE_SUBTOTAL_WITH_DISCOUNT);
    }

    /**
     * Set subtotal in base currency with applied discount
     *
     * @param float $baseSubtotalWithDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount)
    {
        return $this->setData(self::KEY_BASE_SUBTOTAL_WITH_DISCOUNT, $baseSubtotalWithDiscount);
    }

    /**
     * Get shipping amount in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getShippingAmount()
    {
        return $this->getData(self::KEY_SHIPPING_AMOUNT);
    }

    /**
     * Set shipping amount in quote currency
     *
     * @param float $shippingAmount
     * @return $this
     * @since 2.0.0
     */
    public function setShippingAmount($shippingAmount)
    {
        return $this->setData(self::KEY_SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     * Get shipping amount in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(self::KEY_BASE_SHIPPING_AMOUNT);
    }

    /**
     * Set shipping amount in base currency
     *
     * @param float $baseShippingAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseShippingAmount($baseShippingAmount)
    {
        return $this->setData(self::KEY_BASE_SHIPPING_AMOUNT, $baseShippingAmount);
    }

    /**
     * Get shipping discount amount in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getShippingDiscountAmount()
    {
        return $this->getData(self::KEY_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Set shipping discount amount in quote currency
     *
     * @param float $shippingDiscountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setShippingDiscountAmount($shippingDiscountAmount)
    {
        return $this->setData(self::KEY_SHIPPING_DISCOUNT_AMOUNT, $shippingDiscountAmount);
    }

    /**
     * Get shipping discount amount in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->getData(self::KEY_BASE_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Set shipping discount amount in base currency
     *
     * @param float $baseShippingDiscountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseShippingDiscountAmount($baseShippingDiscountAmount)
    {
        return $this->setData(self::KEY_BASE_SHIPPING_DISCOUNT_AMOUNT, $baseShippingDiscountAmount);
    }

    /**
     * Get tax amount in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxAmount()
    {
        return $this->getData(self::KEY_TAX_AMOUNT);
    }

    /**
     * Set tax amount in quote currency
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
     * Get tax amount in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(self::KEY_BASE_TAX_AMOUNT);
    }

    /**
     * Set tax amount in base currency
     *
     * @param float $baseTaxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        return $this->setData(self::KEY_BASE_TAX_AMOUNT, $baseTaxAmount);
    }

    /**
     * Returns the total weee tax applied amount in quote currency.
     *
     * @return float Item weee tax applied amount in quote currency.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(self::KEY_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Sets the total weee tax applied amount in quote currency.
     *
     * @param float $weeeTaxAppliedAmount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount)
    {
        return $this->setData(self::KEY_WEEE_TAX_APPLIED_AMOUNT, $weeeTaxAppliedAmount);
    }

    /**
     * Get shipping tax amount in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getShippingTaxAmount()
    {
        return $this->getData(self::KEY_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Set shipping tax amount in quote currency
     *
     * @param float $shippingTaxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setShippingTaxAmount($shippingTaxAmount)
    {
        return $this->setData(self::KEY_SHIPPING_TAX_AMOUNT, $shippingTaxAmount);
    }

    /**
     * Get shipping tax amount in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->getData(self::KEY_BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Set shipping tax amount in base currency
     *
     * @param float $baseShippingTaxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseShippingTaxAmount($baseShippingTaxAmount)
    {
        return $this->setData(self::KEY_BASE_SHIPPING_TAX_AMOUNT, $baseShippingTaxAmount);
    }

    /**
     * Get subtotal including tax in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getSubtotalInclTax()
    {
        return $this->getData(self::KEY_SUBTOTAL_INCL_TAX);
    }

    /**
     * Set subtotal including tax in quote currency
     *
     * @param float $subtotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setSubtotalInclTax($subtotalInclTax)
    {
        return $this->setData(self::KEY_SUBTOTAL_INCL_TAX, $subtotalInclTax);
    }

    /**
     * Get subtotal including tax in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->getData(self::KEY_BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Set subtotal including tax in base currency
     *
     * @param float $baseSubtotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax)
    {
        return $this->setData(self::KEY_BASE_SUBTOTAL_INCL_TAX, $baseSubtotalInclTax);
    }

    /**
     * Get shipping including tax in quote currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getShippingInclTax()
    {
        return $this->getData(self::KEY_SHIPPING_INCL_TAX);
    }

    /**
     * Set shipping including tax in quote currency
     *
     * @param float $shippingInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setShippingInclTax($shippingInclTax)
    {
        return $this->setData(self::KEY_SHIPPING_INCL_TAX, $shippingInclTax);
    }

    /**
     * Get shipping including tax in base currency
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseShippingInclTax()
    {
        return $this->getData(self::KEY_BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Set shipping including tax in base currency
     *
     * @param float $baseShippingInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setBaseShippingInclTax($baseShippingInclTax)
    {
        return $this->setData(self::KEY_BASE_SHIPPING_INCL_TAX, $baseShippingInclTax);
    }

    /**
     * Get base currency code
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::KEY_BASE_CURRENCY_CODE);
    }

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * Get quote currency code
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getQuoteCurrencyCode()
    {
        return $this->getData(self::KEY_QUOTE_CURRENCY_CODE);
    }

    /**
     * Get quote currency code
     *
     * @param string $quoteCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode)
    {
        return $this->setData(self::KEY_QUOTE_CURRENCY_CODE, $quoteCurrencyCode);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCouponCode()
    {
        return $this->getData(self::KEY_COUPON_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCouponCode($couponCode)
    {
        return $this->setData(self::KEY_COUPON_CODE, $couponCode);
    }

    /**
     * Get items qty
     *
     * @return int||null
     * @since 2.0.0
     */
    public function getItemsQty()
    {
        return $this->getData(self::KEY_ITEMS_QTY);
    }

    /**
     * Set items qty
     *
     * @param int $itemsQty
     * @return $this
     * @since 2.0.0
     */
    public function setItemsQty($itemsQty = null)
    {
        return $this->setData(self::KEY_ITEMS_QTY, $itemsQty);
    }

    /**
     * Get totals by items
     *
     * @return \Magento\Quote\Api\Data\TotalsItemInterface[]|null
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Get totals by items
     *
     * @param \Magento\Quote\Api\Data\TotalsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTotalSegments()
    {
        return $this->getData(self::KEY_TOTAL_SEGMENTS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTotalSegments($totals = [])
    {
        return $this->setData(self::KEY_TOTAL_SEGMENTS, $totals);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\TotalsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
