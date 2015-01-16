<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
/**
 * Cart Totals
 *
 * @codeCoverageIgnore
 */
class Totals extends AbstractExtensibleModel implements TotalsInterface
{
    /* TOTALS */
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';

    /* DISCOUNT */
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const SUBTOTAL_WITH_DISCOUNT = 'subtotal_with_discount';
    const BASE_SUBTOTAL_WITH_DISCOUNT = 'base_subtotal_with_discount';

    /* SHIPPING */
    const SHIPPING_AMOUNT = 'shipping_amount';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const SHIPPING_DISCOUNT_AMOUNT = 'shipping_discount_amount';
    const BASE_SHIPPING_DISCOUNT_AMOUNT = 'base_shipping_discount_amount';

    /* TAX */
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';

    /* CURRENCY */
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const QUOTE_CURRENCY_CODE = 'quote_currency_code';

    /* ITEMS */
    const ITEMS = 'items';

    /**
     * Get grand total in quote currency
     *
     * @return float|null
     */
    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
    }

    /**
     * Get grand total in base currency
     *
     * @return float|null
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(self::BASE_GRAND_TOTAL);
    }

    /**
     * Get subtotal in quote currency
     *
     * @return float|null
     */
    public function getSubtotal()
    {
        return $this->getData(self::SUBTOTAL);
    }

    /**
     * Get subtotal in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotal()
    {
        return $this->getData(self::BASE_SUBTOTAL);
    }

    /**
     * Get discount amount in quote currency
     *
     * @return float|null
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * Get discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Get subtotal in quote currency with applied discount
     *
     * @return float|null
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getData(self::SUBTOTAL_WITH_DISCOUNT);
    }

    /**
     * Get subtotal in base currency with applied discount
     *
     * @return float|null
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getData(self::BASE_SUBTOTAL_WITH_DISCOUNT);
    }

    /**
     * Get shipping amount in quote currency
     *
     * @return float|null
     */
    public function getShippingAmount()
    {
        return $this->getData(self::SHIPPING_AMOUNT);
    }

    /**
     * Get shipping amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Get shipping discount amount in quote currency
     *
     * @return float|null
     */
    public function getShippingDiscountAmount()
    {
        return $this->getData(self::SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Get shipping discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->getData(self::BASE_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Get tax amount in quote currency
     *
     * @return float|null
     */
    public function getTaxAmount()
    {
        return $this->getData(self::TAX_AMOUNT);
    }

    /**
     * Get tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(self::BASE_TAX_AMOUNT);
    }

    /**
     * Get shipping tax amount in quote currency
     *
     * @return float|null
     */
    public function getShippingTaxAmount()
    {
        return $this->getData(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * Get shipping tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->getData(self::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Get subtotal including tax in quote currency
     *
     * @return float|null
     */
    public function getSubtotalInclTax()
    {
        return $this->getData(self::SUBTOTAL_INCL_TAX);
    }

    /**
     * Get subtotal including tax in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->getData(self::BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Get shipping including tax in quote currency
     *
     * @return float|null
     */
    public function getShippingInclTax()
    {
        return $this->getData(self::SHIPPING_INCL_TAX);
    }

    /**
     * Get shipping including tax in base currency
     *
     * @return float|null
     */
    public function getBaseShippingInclTax()
    {
        return $this->getData(self::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::BASE_CURRENCY_CODE);
    }

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode()
    {
        return $this->getData(self::QUOTE_CURRENCY_CODE);
    }

    /**
     * Get totals by items
     *
     * @return \Magento\Quote\Api\Data\TotalsItemInterface[]|null
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }
}
