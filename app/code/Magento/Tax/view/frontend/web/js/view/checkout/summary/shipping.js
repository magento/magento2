/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/shipping',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, quote) {
        var displayMode = window.checkoutConfig.reviewShippingDisplayMode;
        return Component.extend({
            defaults: {
                displayMode: displayMode,
                template: 'Magento_Tax/checkout/summary/shipping'
            },
            isBothPricesDisplayed: function() {
                return 'both' == this.displayMode
            },
            isIncludingDisplayed: function() {
                return 'including' == this.displayMode;
            },
            isExcludingDisplayed: function() {
                return 'excluding' == this.displayMode;
            },
            getIncludingValue: function() {
                if (!this.isFullMode()) {
                    return this.notCalculatedMessage;
                }
                var price = 0;
                if (this.totals() && quote.shippingMethod()) {
                    price =  this.totals().shipping_incl_tax;
                } else {
                    return this.notCalculatedMessage;
                }
                return this.getFormattedPrice(price);
            },
            getExcludingValue: function() {
                if (!this.isFullMode()) {
                    return this.notCalculatedMessage;
                }
                var price = 0;
                if (this.totals() && quote.shippingMethod()) {
                    price =  this.totals().shipping_amount;
                } else {
                    return this.notCalculatedMessage;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
