/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;
        return Component.extend({
            defaults: {
                displaySubtotalMode: displaySubtotalMode,
                template: 'Magento_Tax/checkout/summary/subtotal'
            },
            totals: quote.getTotals(),
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().subtotal;
                }
                return this.getFormattedPrice(price);
            },
            isBothPricesDisplayed: function() {
                return 'both' == this.displaySubtotalMode;
            },
            isIncludingTaxDisplayed: function() {
                return 'including' == this.displaySubtotalMode;
            },
            getValueInclTax: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().subtotal_incl_tax;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
