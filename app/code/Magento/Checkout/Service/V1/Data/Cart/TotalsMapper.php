<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

use Magento\Sales\Model\Quote;

/**
 * Totals data mapper
 */
class TotalsMapper
{
    /**
     * Fetch quote totals data
     *
     * @param Quote $quote
     * @return array
     */
    public function map(Quote $quote)
    {
        $totals = [
            Totals::BASE_GRAND_TOTAL => $quote->getBaseGrandTotal(),
            Totals::GRAND_TOTAL => $quote->getGrandTotal(),
            Totals::BASE_SUBTOTAL => $quote->getBaseSubtotal(),
            Totals::SUBTOTAL => $quote->getSubtotal(),
            Totals::BASE_SUBTOTAL_WITH_DISCOUNT => $quote->getBaseSubtotalWithDiscount(),
            Totals::SUBTOTAL_WITH_DISCOUNT => $quote->getSubtotalWithDiscount(),

            Totals::BASE_CURRENCY_CODE => $quote->getBaseCurrencyCode(),
            Totals::QUOTE_CURRENCY_CODE => $quote->getQuoteCurrencyCode(),
        ];

        $shippingAddress = $quote->getShippingAddress();

        $totals[Totals::DISCOUNT_AMOUNT] = $shippingAddress->getDiscountAmount();
        $totals[Totals::BASE_DISCOUNT_AMOUNT] = $shippingAddress->getBaseDiscountAmount();
        $totals[Totals::SHIPPING_AMOUNT] = $shippingAddress->getShippingAmount();
        $totals[Totals::BASE_SHIPPING_AMOUNT] = $shippingAddress->getBaseShippingAmount();
        $totals[Totals::SHIPPING_DISCOUNT_AMOUNT] = $shippingAddress->getShippingDiscountAmount();
        $totals[Totals::BASE_SHIPPING_DISCOUNT_AMOUNT] = $shippingAddress->getBaseShippingDiscountAmount();
        $totals[Totals::TAX_AMOUNT] = $shippingAddress->getTaxAmount();
        $totals[Totals::BASE_TAX_AMOUNT] = $shippingAddress->getBaseTaxAmount();
        $totals[Totals::SHIPPING_TAX_AMOUNT] = $shippingAddress->getShippingTaxAmount();
        $totals[Totals::BASE_SHIPPING_TAX_AMOUNT] = $shippingAddress->getBaseShippingTaxAmount();
        $totals[Totals::SUBTOTAL_INCL_TAX] = $shippingAddress->getSubtotalInclTax();
        $totals[Totals::BASE_SUBTOTAL_INCL_TAX] = $shippingAddress->getBaseSubtotalTotalInclTax();
        $totals[Totals::SHIPPING_INCL_TAX] = $shippingAddress->getShippingInclTax();
        $totals[Totals::BASE_SHIPPING_INCL_TAX] = $shippingAddress->getBaseShippingInclTax();
        return $totals;
    }
}
