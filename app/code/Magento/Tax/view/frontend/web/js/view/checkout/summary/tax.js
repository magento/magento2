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
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function (ko, Component, quote, totals, priceUtils) {
        "use strict";
        var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal;
        var isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed;
        var isZeroTaxDisplayed = window.checkoutConfig.isZeroTaxDisplayed;
        return Component.extend({
            defaults: {
                isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
                notCalculatedMessage: 'Not yet calculated',
                template: 'Magento_Tax/checkout/summary/tax'
            },
            totals: quote.getTotals(),
            isFullTaxSummaryDisplayed: isFullTaxSummaryDisplayed,
            getTitle: function() {
                return "Tax";
            },
            ifShowValue: function() {
                if (!isTaxDisplayedInGrandTotal) {
                    return false;
                }
                if (this.getPureValue() == 0) {
                    return isZeroTaxDisplayed;
                }
                return true;
            },
            ifShowDetails: function() {
                return isTaxDisplayedInGrandTotal && this.getPureValue() > 0 && isFullTaxSummaryDisplayed;
            },
            getPureValue: function() {
                var amount = 0;
                if (this.totals()) {
                    var taxTotal = totals.getTotalByCode('tax');
                    if (taxTotal) {
                        amount = taxTotal.value;
                    }
                }
                return amount;
            },
            getValue: function() {
                var amount = 0;
                if (this.totals()) {
                    var taxTotal = totals.getTotalByCode('tax');
                    if (taxTotal) {
                        amount = taxTotal.value;
                    } else {
                        return this.notCalculatedMessage;
                    }
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
            }
        });
    }
);
