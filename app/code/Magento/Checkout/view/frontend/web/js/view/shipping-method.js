/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        '../model/quote',
        '../model/shipping-service',
        '../action/select-shipping-method',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, _, Component, quote, shippingService, selectShippingMethod, priceUtils, navigator) {
        var stepName = 'shippingMethod';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
            },
            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },
            stepNumber: navigator.getStepNumber(stepName),
            rates: shippingService.getSippingRates(),
            // Checkout step navigation
            isVisible: navigator.isStepVisible(stepName),
            quoteHasShippingAddress: function() {
                return quote.isVirtual() || quote.getShippingAddress();
            },

            selectedMethod: quote.getSelectedShippingMethod(),
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
                        callbacks.push(view.afterSelect);
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
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },
            backToShippingAddress: function () {
                navigator.setCurrent(stepName).goBack();
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            }
        });
    }
);
