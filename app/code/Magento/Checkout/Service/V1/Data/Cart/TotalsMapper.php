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

use \Magento\Sales\Model\Quote;

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
