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
        return Component.extend({
            defaults: {
                isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
                template: 'Magento_Tax/checkout/review/tax_total'
            },
            getColspan: 3,
            totals: quote.getTotals(),
            style: "",
            getTitle: function() {
                return "Tax";
            },
            getValue: function() {
                var amount = 0;
                if (quote.getTotals()) {
                    amount = this.totals().tax_amount;
                }
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            }
        });
    }
);
