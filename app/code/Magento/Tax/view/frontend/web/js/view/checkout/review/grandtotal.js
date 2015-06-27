/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            exclTaxLabel: 'Grand Total Excl. Tax',
            inclTaxLabel: 'Grand Total Incl. Tax',
            basicCurrencyMessage: 'Your credit card will be charged for',
            getTitle: function() {
                return "Grand Total";
            },
            totals: quote.getTotals(),
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().grand_total;
                }
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_grand_total;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
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
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
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
