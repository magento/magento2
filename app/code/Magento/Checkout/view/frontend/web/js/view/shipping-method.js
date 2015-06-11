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
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Catalog/js/price-utils',
        '../action/set-shipping-information'
    ],
    function (
        $,
        ko,
        _,
        Component,
        quote,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        priceUtils,
        setShippingInformation
    ) {
        var rates = window.checkoutConfig.shippingRates.data;
        var rateKey = window.checkoutConfig.shippingRates.key;
        if (rateKey) {
            rateRegistry.set(rateKey, rates);
        }
        selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);
        shippingService.setShippingRates(rates);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
            },
            rates: shippingService.getSippingRates(),
            isVisible: ko.observable(true),
            isLoading: shippingService.isLoading,

            isSelected: ko.computed(function () {
                    return quote.shippingMethod()
                        ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),
            selectShippingMethod: function(shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                return true;
            },

            quoteHasShippingAddress: function() {
                return quote.isVirtual() || quote.getShippingAddress();
            },

            setShippingInformation: function () {
                var item,
                    customOptions = {};
                for (item in this.elems()) {
                    if ('submit' in this.elems()[item]) {
                        customOptions = _.extend(customOptions, this.elems()[item].submit());
                    }
                }
                setShippingInformation(customOptions, this.getAfterSelectCallbacks());
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
