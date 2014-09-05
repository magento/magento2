<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Cart Totals Builder
 *
 * @codeCoverageIgnore
 */
class TotalsBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set grand total in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setGrandTotal($value)
    {
        return $this->_set(Totals::GRAND_TOTAL, $value);
    }

    /**
     * Set grand total in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseGrandTotal($value)
    {
        return $this->_set(Totals::BASE_GRAND_TOTAL, $value);
    }

    /**
     * Set subtotal in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setSubtotal($value)
    {
        return $this->_set(Totals::SUBTOTAL, $value);
    }

    /**
     * Set subtotal in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseSubtotal($value)
    {
        return $this->_set(Totals::BASE_SUBTOTAL, $value);
    }

    /**
     * Set discount amount in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setDiscountAmount($value)
    {
        return $this->_set(Totals::DISCOUNT_AMOUNT, $value);
    }

    /**
     * Set discount amount in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseDiscountAmount($value)
    {
        return $this->_set(Totals::BASE_DISCOUNT_AMOUNT, $value);
    }

    /**
     * Set subtotal in quote currency with applied discount
     *
     * @param float|null $value
     * @return $this
     */
    public function setSubtotalWithDiscount($value)
    {
        return $this->_set(Totals::SUBTOTAL_WITH_DISCOUNT, $value);
    }

    /**
     * Set subtotal in base currency with applied discount
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseSubtotalWithDiscount($value)
    {
        return $this->_set(Totals::BASE_SUBTOTAL_WITH_DISCOUNT, $value);
    }

    /**
     * Set shipping amount in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setShippingAmount($value)
    {
        return $this->_set(Totals::SHIPPING_AMOUNT, $value);
    }

    /**
     * Set shipping amount in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseShippingAmount($value)
    {
        return $this->_set(Totals::BASE_SHIPPING_AMOUNT, $value);
    }

    /**
     * Set shipping discount amount in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setShippingDiscountAmount($value)
    {
        return $this->_set(Totals::SHIPPING_DISCOUNT_AMOUNT, $value);
    }

    /**
     * Set shipping discount amount in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseShippingDiscountAmount($value)
    {
        return $this->_set(Totals::BASE_SHIPPING_DISCOUNT_AMOUNT, $value);
    }

    /**
     * Set tax amount in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setTaxAmount($value)
    {
        return $this->_set(Totals::TAX_AMOUNT, $value);
    }

    /**
     * Set tax amount in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseTaxAmount($value)
    {
        return $this->_set(Totals::BASE_TAX_AMOUNT, $value);
    }

    /**
     * Set shipping tax amount in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setShippingTaxAmount($value)
    {
        return $this->_set(Totals::SHIPPING_TAX_AMOUNT, $value);
    }

    /**
     * Set shipping tax amount in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseShippingTaxAmount($value)
    {
        return $this->_set(Totals::BASE_SHIPPING_TAX_AMOUNT, $value);
    }

    /**
     * Set subtotal including tax in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setSubtotalInclTax($value)
    {
        return $this->_set(Totals::SUBTOTAL_INCL_TAX, $value);
    }

    /**
     * Set subtotal including tax in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseSubtotalInclTax($value)
    {
        return $this->_set(Totals::BASE_SUBTOTAL_INCL_TAX, $value);
    }

    /**
     * Set shipping including tax in quote currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setShippingInclTax($value)
    {
        return $this->_set(Totals::SHIPPING_INCL_TAX, $value);
    }

    /**
     * Set shipping including tax in base currency
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseShippingInclTax($value)
    {
        return $this->_set(Totals::BASE_SHIPPING_INCL_TAX, $value);
    }

    /**
     * Set base currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setBaseCurrencyCode($value)
    {
        return $this->_set(Currency::BASE_CURRENCY_CODE, $value);
    }

    /**
     * Set quote currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setQuoteCurrencyCode($value)
    {
        return $this->_set(Currency::QUOTE_CURRENCY_CODE, $value);
    }

    /**
     * Set items totals info
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Totals\Item[]|null $value
     * @return $this
     */
    public function setItems($value)
    {
        return $this->_set(Totals::ITEMS, $value);
    }
}
