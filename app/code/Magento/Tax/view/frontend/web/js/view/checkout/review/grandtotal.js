/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        "use strict";
        var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal || false;
        var isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed || false;
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: isFullTaxSummaryDisplayed,
                template: 'Magento_Tax/checkout/review/grandtotal'
            },
            getColspan: 3,
            style: "",
            getTitle: function() {
                return "Grand Total"
            },
            totals: quote.getTotals(),
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().grand_total;
                }
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_grand_total;
                }
                return quote.getBaseCurrencySymbol() + priceUtils.formatPrice(price);
            },
            isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
            getGrandTotalExclTax: function() {
                var totals = this.totals();
                if (!totals) {
                    return 0;
                }
                var amount = totals.grand_total - totals.tax_amount;
                if (amount < 0) {
                    return 0;
                }
                return quote.getCurrencySymbol() + priceUtils.formatPrice(amount);
            },
            isBaseGrandTotalDisplayNeeded: function() {
                var totals = this.totals();
                if (!totals) {
                    return false;
                }
                return totals.base_currency_code != totals.quote_currency_code;
            }
        });
    }
);
