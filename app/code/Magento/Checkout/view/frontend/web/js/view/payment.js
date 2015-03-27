/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/form/component',
        '../model/quote',
        '../model/payment-service',
        '../action/select-payment-method',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, paymentService, selectPaymentMethod, navigator) {
        var stepName = 'paymentMethod';
        var paymentMethods = paymentService.getAvailablePaymentMethods();
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment'
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
            getAvailablePaymentMethods: function() {
                return paymentMethods();
            },
            isVisible: navigator.isStepVisible(stepName),
            backToShippingMethod: function() {
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
