/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        '../model/quote',
        '../model/shipping-service',
        '../action/select-shipping-method',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, ko, _, Component, quote, shippingService, selectShippingMethod, priceUtils) {
        var rates = window.checkoutConfig.shippingRates;
        shippingService.setShippingRates(rates);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
            },
            rates: shippingService.getSippingRates(),
            isVisible: ko.observable(true),
            selectedMethod: quote.getSelectedShippingMethod(),

            quoteHasShippingAddress: function() {
                return quote.isVirtual() || quote.getShippingAddress();
            },

            verifySelectedMethodCode: function (data) {
                if (this.selectedMethod() == data) {
                    return data;
                }
                return false;
            },

            setShippingMethod: function (form) {
                var item,
                    customOptions = {};
                for (item in this.elems()) {
                    if ('submit' in this.elems()[item]) {
                        customOptions = _.extend(customOptions, this.elems()[item].submit());
                    }
                }
                form = $(form);
                var code = form.find("input[name='shipping_method']:checked").val();
                selectShippingMethod(code, customOptions, this.getAfterSelectCallbacks());
            },

            getAfterSelectCallbacks: function() {
                var callbacks = [];
                _.each(this.getAdditionalMethods(), function(view) {
                    if (typeof view.afterSelect === 'function') {
                        callbacks.push(view.afterSelect.bind(view));
                    }
                });
                return callbacks;
            },

            getAdditionalMethods: function() {
                var methods = [];
                _.each(this.getRegion('afterSelect')(), function(elem) {
                    methods = _.union(methods, elem.elems());
                });
                return methods;
            },

            isActive: function() {
                return !quote.isVirtual();
            }
        });
    }
);
