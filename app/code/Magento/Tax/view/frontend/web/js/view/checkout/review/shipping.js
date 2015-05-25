/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function ($, Component, quote, priceUtils, shippingService) {
        var displayMode = window.checkoutConfig.reviewShippingDisplayMode;
        return Component.extend({
            defaults: {
                displayMode: displayMode,
                template: 'Magento_Tax/checkout/review/shipping'
            },
            getColspan: 3,
            style: "",
            notCalculatedMessage: 'Not yet calculated',
            quoteIsVirtual: quote.isVirtual(),
            selectedShippingMethod: quote.getShippingMethod(),
            getTitle: function() {
                return "Shipping & Handling" + "(" + shippingService.getTitleByCode(this.selectedShippingMethod()) + ")";
            },
            getExcludingLabel: function() {
                return "Shipping Excl. Tax" + "(" + shippingService.getTitleByCode(this.selectedShippingMethod()) + ")";
            },
            getIncludingLabel: function() {
                return "Shipping Incl. Tax" + "(" + shippingService.getTitleByCode(this.selectedShippingMethod()) + ")";
            },
            totals: quote.getTotals(),
            isBothPricesDisplayed: function() {
                return 'both' == this.displayMode
            },
            isIncludingDisplayed: function() {
                return 'including' == this.displayMode;
            },
            isExcludingDisplayed: function() {
                return 'excluding' == this.displayMode;
            },
            getValue: function() {
                var price = 0;
                if (this.totals() && this.totals().shipping_amount) {
                    price =  this.totals().shipping_amount;
                } else {
                    return this.notCalculatedMessage;
                }
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getIncludingValue: function() {
                var price = 0;
                if (this.totals() && this.totals().shipping_incl_tax) {
                    price =  this.totals().shipping_incl_tax;
                } else {
                    return this.notCalculatedMessage;
                }
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getExcludingValue: function() {
                var price = 0;
                if (this.totals() && this.totals().shipping_amount) {
                    price =  this.totals().shipping_amount;
                } else {
                    return this.notCalculatedMessage;
                }
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            }
        });
    }
);
