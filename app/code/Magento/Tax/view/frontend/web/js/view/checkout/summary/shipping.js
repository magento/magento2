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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function ($, Component, quote, shippingService) {
        var displayMode = window.checkoutConfig.reviewShippingDisplayMode;
        return Component.extend({
            defaults: {
                displayMode: displayMode,
                template: 'Magento_Tax/checkout/summary/shipping'
            },
            getExcludingLabel: function() {
                return "Shipping Excl. Tax";
            },
            getIncludingLabel: function() {
                return "Shipping Incl. Tax";
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
                var price = 0;
                if (this.totals() && this.totals().shipping_incl_tax) {
                    price =  this.totals().shipping_incl_tax;
                } else {
                    return this.notCalculatedMessage;
                }
                return this.getFormattedPrice(price);
            },
            getExcludingValue: function() {
                var price = 0;
                if (this.totals() && this.totals().shipping_amount) {
                    price =  this.totals().shipping_amount;
                } else {
                    return this.notCalculatedMessage;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
