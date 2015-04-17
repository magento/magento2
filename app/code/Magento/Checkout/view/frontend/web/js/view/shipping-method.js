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
        '../model/quote',
        '../model/shipping-service',
        '../action/select-shipping-method',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, shippingService, selectShippingMethod, priceUtils, navigator) {
        var stepName = 'shippingMethod';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
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
                form = $(form);
                var code = form.find("input[name='shipping_method']:checked").val();
                selectShippingMethod(code);
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
