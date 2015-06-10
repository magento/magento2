/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function ($, Component, quote, shippingService) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/shipping'
            },
            notCalculatedMessage: 'Not yet calculated',
            quoteIsVirtual: quote.isVirtual(),
            totals: quote.getTotals(),
            title: 'Shipping',
            getShippingMethodTitle: function() {
                return shippingService.getTitleByCode(quote.shippingMethod())
            },
            getValue: function() {
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
