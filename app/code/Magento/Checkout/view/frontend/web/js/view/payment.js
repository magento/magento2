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
        'Magento_Checkout/js/model/payment-service'
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
            paymentForm: '#co-payment-form',
            initObservable: function () {
                this._super()
                    .observe('activeMethod');
                return this;
            },
            quoteHasShippingMethod: function() {
                return quote.isVirtual() || quote.getShippingMethod();
            },
            setPaymentMethod: function() {
                if (!this.activeMethod()) {
                    alert('Please specify payment method.');
                    return;
                }
                if (this.isFormValid()) {
                    selectPaymentMethod(this.getActiveMethodView());
                }
            },
            getAvailableViews: function () {
                var sortedElems = [],
                    self = this;

                _.each(paymentService.getAvailablePaymentMethods()(), function (originElem) {
                    var method = self.getMethodViewByCode(originElem.code);
                    if (method && method.isAvailable()) {
                        sortedElems.push(method);
                    }
                });

                return sortedElems;
            },
            getMethodViewByCode: function(code) {
                return _.find(this.getRegion('paymentMethods')(), function(elem) {
                    return elem.getCode() == code;
                });
            },
            getActiveMethodView: function() {
                return this.getMethodViewByCode(this.activeMethod());
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
            },
            isFormValid: function() {
                $(this.paymentForm).validation();
                return $(this.paymentForm).validation('isValid');
            },
            getFormKey: function() {
                return window.checkoutConfig.formKey;
            }
        });
    }
);
