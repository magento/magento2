/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (ko, Component, quote, priceUtils) {
        "use strict";
        var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal;
        var isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed;
        return Component.extend({
            defaults: {
                isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
                template: 'Magento_Tax/checkout/review/tax_total'
            },
            colspan: 3,
            totals: quote.getTotals(),
            style: "",
            isFullTaxSummaryDisplayed: isFullTaxSummaryDisplayed,
            lastTaxGroupId: null,
            isDetailsVisible: ko.observable(),
            getTitle: function() {
                return "Tax";
            },
            getValue: function() {
                var amount = 0;
                if (this.totals()) {
                    amount = this.totals().tax_amount;
                }
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },
            formatPrice: function(amount) {
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },
            getDetails: function() {
                var totals = this.totals();
                if (totals.extension_attributes) {
                    return totals.extension_attributes.tax_grandtotal_details;
                }
                return [];
            },
            toggleDetails: function() {
                this.isDetailsVisible(!this.isDetailsVisible());
            }
        });
    }
);
