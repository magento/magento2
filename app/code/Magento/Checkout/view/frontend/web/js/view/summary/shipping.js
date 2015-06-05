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
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/shipping'
            },
            colspan: 3,
            style: "",
            notCalculatedMessage: 'Not yet calculated',
            quoteIsVirtual: quote.isVirtual(),
            selectedShippingMethod: quote.getSelectedShippingMethod(),
            totals: quote.getTotals(),
            title: 'Shipping',
            getShippingMethodTitle: function() {
                return shippingService.getTitleByCode(this.selectedShippingMethod())
            },
            getValue: function() {
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
