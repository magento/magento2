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
        '../action/select-payment-method',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, selectPaymentMethod, navigator) {
        var stepName = 'paymentMethod';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment',
                activeMethod: ''
            },
            initObservable: function () {
                this._super()
                    .observe('activeMethod');
                return this;
            },
            stepNumber: navigator.getStepNumber(stepName),
            quoteHasShippingMethod: function() {
                return quote.isVirtual() || quote.getShippingMethod();
            },
            setPaymentMethod: function(form) {
                var paymentMethodCode = $("input[name='payment[method]']:checked", form).val();
                if (!paymentMethodCode) {
                    alert('Please specify payment method.');
                }
                selectPaymentMethod(paymentMethodCode, []);
            },
            getAvailableViews: function () {
              return this.elems().filter(function(elem) {
                  return elem.isAvailable();
              });
            },
            isVisible: navigator.isStepVisible(stepName),
            backToShippingMethod: function() {
                navigator.setCurrent(stepName).goBack();
            },
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            },
            isMethodActive: function(code) {
                return this.activeMethod() === code;
            }
        });
    }
);
