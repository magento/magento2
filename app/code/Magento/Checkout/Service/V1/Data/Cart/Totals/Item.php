<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart\Totals;

/**
 * Cart item totals.
 *
 * @codeCoverageIgnore
 */
class Item extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Price.
     */
    const PRICE = 'price';

    /**
     * Base price.
     */
    const BASE_PRICE = 'base_price';

    /**
     * Quantity.
     */
    const QTY = 'qty';

    /**
     * Row total.
     */
    const ROW_TOTAL = 'row_total';

    /**
     * Base row total.
     */
    const BASE_ROW_TOTAL = 'base_row_total';

    /**
     * Row total with discount.
     */
    const ROW_TOTAL_WITH_DISCOUNT = 'row_total_with_discount';

    /**
     * Discount amount.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * Base discount amount.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';

    /**
     * Discount percent.
     */
    const DISCOUNT_PERCENT = 'discount_percent';

    /**
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';

    /**
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';

    /**
     * Tax percent.
     */
    const TAX_PERCENT = 'tax_percent';

    /**
     * Price including tax.
     */
    const PRICE_INCL_TAX = 'price_incl_tax';

    /**
     * Base price including tax.
     */
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';

    /**
     * Row total including tax.
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    /**
     * Base row total including tax.
     */
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';

    /**
     * Returns the item price in quote currency.
     *
     * @return float Item price in quote currency.
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Returns the item price in base currency.
     *
     * @return float Item price in base currency.
     */
    public function getBasePrice()
    {
        return $this->_get(self::BASE_PRICE);
    }

    /**
     * Returns the item quantity.
     *
     * @return int Item quantity.
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * Returns the row total in quote currency.
     *
     * @return float Row total in quote currency.
     */
    public function getRowTotal()
    {
        return $this->_get(self::ROW_TOTAL);
    }

    /**
     * Returns the row total in base currency.
     *
     * @return float Row total in base currency.
     */
    public function getBaseRowTotal()
    {
        return $this->_get(self::BASE_ROW_TOTAL);
    }

    /**
     * Returns the row total with discount in quote currency.
     *
     * @return float|null Row total with discount in quote currency. Otherwise, null.
     */
    public function getRowTotalWithDiscount()
    {
        return $this->_get(self::ROW_TOTAL_WITH_DISCOUNT);
    }

    /**
     * Returns the tax amount in quote currency.
     *
     * @return float|null Tax amount in quote currency. Otherwise, null.
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * Returns the tax amount in base currency.
     *
     * @return float|null Tax amount in base currency. Otherwise, null.
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::BASE_TAX_AMOUNT);
    }

    /**
     * Returns the tax percent.
     *
     * @return int|null Tax percent. Otherwise, null.
     */
    public function getTaxPercent()
    {
        return $this->_get(self::TAX_PERCENT);
    }

    /**
     * Returns the discount amount in quote currency.
     *
     * @return float|null Discount amount in quote currency. Otherwise, null.
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Returns the discount amount in base currency.
     *
     * @return float|null Discount amount in base currency. Otherwise, null.
     */
    public function getBaseDiscountAmount()
    {
        return $this->_get(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns the discount percent.
     *
     * @return int|null Discount percent. Otherwise, null.
     */
    public function getDiscountPercent()
    {
        return $this->_get(self::DISCOUNT_PERCENT);
    }

    /**
     * Returns the price including tax in quote currency.
     *
     * @return float|null Price including tax in quote currency. Otherwise, null.
     */
    public function getPriceInclTax()
    {
        return $this->_get(self::PRICE_INCL_TAX);
    }

    /**
     * Returns the price including tax in base currency.
     *
     * @return float|null Price including tax in base currency. Otherwise, null.
     */
    public function getBasePriceInclTax()
    {
        return $this->_get(self::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns the row total including tax in quote currency.
     *
     * @return float|null Row total including tax in quote currency. Otherwise, null.
     */
    public function getRowTotalInclTax()
    {
        return $this->_get(self::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns the row total including tax in base currency.
     *
     * @return float|null Row total including tax in base currency. Otherwise, null.
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->_get(self::BASE_ROW_TOTAL_INCL_TAX);
    }
}
