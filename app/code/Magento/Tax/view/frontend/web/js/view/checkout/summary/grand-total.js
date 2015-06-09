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
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Magento_Tax/checkout/summary/grand-total'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
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
            getGrandTotalExclTax: function() {
                var totals = this.totals();
                if (!totals) {
                    return 0;
                }
                return priceUtils.formatPrice(totals.grand_total, quote.getPriceFormat());
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
