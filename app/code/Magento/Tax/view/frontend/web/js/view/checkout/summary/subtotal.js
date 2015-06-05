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
        var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;
        return Component.extend({
            defaults: {
                displaySubtotalMode: displaySubtotalMode,
                template: 'Magento_Tax/checkout/summary/subtotal'
            },
            colspan: 3,
            style: "",
            excludingTaxMessage: 'Cart Subtotal (Excl. Tax)',
            includingTaxMessage: 'Cart Subtotal (Incl. Tax)',
            getTitle: function() {
                return "Cart Subtotal"
            },
            totals: quote.getTotals(),
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().subtotal;
                }
                return priceUtils.formatPrice(price, quote.getPriceFormat());
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
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            }
        });
    }
);
