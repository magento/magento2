<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart\Totals
 */
interface TotalsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get grand total in quote currency
     *
     * @return float|null
     */
    public function getGrandTotal();

    /**
     * Get grand total in base currency
     *
     * @return float|null
     */
    public function getBaseGrandTotal();

    /**
     * Get subtotal in quote currency
     *
     * @return float|null
     */
    public function getSubtotal();

    /**
     * Get subtotal in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotal();

    /**
     * Get discount amount in quote currency
     *
     * @return float|null
     */
    public function getDiscountAmount();

    /**
     * Get discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseDiscountAmount();

    /**
     * Get subtotal in quote currency with applied discount
     *
     * @return float|null
     */
    public function getSubtotalWithDiscount();

    /**
     * Get subtotal in base currency with applied discount
     *
     * @return float|null
     */
    public function getBaseSubtotalWithDiscount();

    /**
     * Get shipping amount in quote currency
     *
     * @return float|null
     */
    public function getShippingAmount();

    /**
     * Get shipping amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingAmount();

    /**
     * Get shipping discount amount in quote currency
     *
     * @return float|null
     */
    public function getShippingDiscountAmount();

    /**
     * Get shipping discount amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingDiscountAmount();

    /**
     * Get tax amount in quote currency
     *
     * @return float|null
     */
    public function getTaxAmount();

    /**
     * Get tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseTaxAmount();

    /**
     * Get shipping tax amount in quote currency
     *
     * @return float|null
     */
    public function getShippingTaxAmount();

    /**
     * Get shipping tax amount in base currency
     *
     * @return float|null
     */
    public function getBaseShippingTaxAmount();

    /**
     * Get subtotal including tax in quote currency
     *
     * @return float|null
     */
    public function getSubtotalInclTax();

    /**
     * Get subtotal including tax in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotalInclTax();

    /**
     * Get shipping including tax in quote currency
     *
     * @return float|null
     */
    public function getShippingInclTax();

    /**
     * Get shipping including tax in base currency
     *
     * @return float|null
     */
    public function getBaseShippingInclTax();

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode();

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode();

    /**
     * Get totals by items
     *
     * @return \Magento\Checkout\Api\Data\TotalsItemInterface[]|null
     */
    public function getItems();
}
