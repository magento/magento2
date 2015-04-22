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
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment-service',
    ],
    function ($, Component, quote, selectPaymentMethod, navigator, paymentService) {
        var stepName = 'paymentMethod';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment',
                activeMethod: ''
            },
            stepNumber: navigator.getStepNumber(stepName),
            isVisible: navigator.isStepVisible(stepName),
            initObservable: function () {
                this._super()
                    .observe('activeMethod');
                return this;
            },
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
                return this.sort(this.elems().filter(function(elem) {
                    return elem.isAvailable();
                }));
            },
            sort: function(elems){
                var sortedElems = [],
                    self = this;

                _.each(paymentService.getAvailablePaymentMethods()(), function (originElem) {
                    sortedElems.push(self.getMethodByCode(elems, originElem.code));
                });
                return sortedElems;
            },
            getMethodByCode: function(elems, code) {
                var method = null;
                _.each(elems, function(elem) {
                    if (elem.getCode() == code) {
                        method = elem;
                        return false;
                    }
                });
                return method;
            },
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
