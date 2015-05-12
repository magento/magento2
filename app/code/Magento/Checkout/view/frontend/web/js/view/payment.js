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
                var self = this,
                    isPaymentSelected = false,
                    availableCodes = this.getAvailableCodes();

                _.each(this.getRegion('paymentMethods')(), function(elem) {
                    if (elem.isEnabled() && _.contains(availableCodes, elem.getCode())) {
                        isPaymentSelected = true;
                    }
                });

                _.each(this.getAdditionalMethods(), function(elem) {
                    if (elem.isActive() && !isPaymentSelected) {
                        self.activeMethod('free');
                    }
                });

                if (!this.activeMethod()) {
                    alert('Please specify payment method.');
                    return;
                }

                if (this.isFormValid()) {
                    selectPaymentMethod(
                        this.getPaymentMethodData(),
                        this.getPaymentMethodInfo(),
                        this.getPaymentMethodCallbacks()
                    );
                }
            },
            getPaymentMethodData: function() {
                var data = _.extend({
                    "method": this.activeMethod(),
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
                }, this.getActiveMethodView().getData());

                _.each(this.getAdditionalMethods(), function(elem) {
                    if (elem.isActive()) {
                        data = _.extend(data, elem.getData());
                    }
                });

                return data;
            },
            getPaymentMethodInfo: function() {
                var info = this.getActiveMethodView().getInfo();

                _.each(this.getAdditionalMethods(), function(elem) {
                    if (elem.isActive()) {
                        info = _.union(info, elem.getInfo());
                    }
                });

                return info;
            },
            getPaymentMethodCallbacks: function() {
                var callbacks = [this.getActiveMethodView().afterSave];

                _.each(this.getAdditionalMethods(), function(elem) {
                    if (elem.isActive()) {
                        callbacks = _.union(callbacks, [elem.afterSave]);
                    }
                });

                return callbacks;
            },
            getFreeMethodView: function() {
                return this.getRegion('freeMethod')()[0];
            },
            getAvailableViews: function () {
                var sortedElems = [],
                    self = this;

                _.each(this.getAvailableMethods(), function (originElem) {
                    var method = self.getMethodViewByCode(originElem.code);
                    if (method && method.isAvailable()) {
                        sortedElems.push(method);
                    }
                });

                if (sortedElems.length == 1) {
                    this.activeMethod(sortedElems[0].getCode());
                }

                return sortedElems;
            },
            getAvailableMethods: function() {
                return paymentService.getAvailablePaymentMethods()();
            },
            getAvailableCodes: function() {
                return _.pluck(this.getAvailableMethods(), 'code');
            },
            getMethodViewByCode: function(code) {
                return _.find(this.getRegion('paymentMethods')(), function(elem) {
                    return elem.getCode() == code;
                });
            },
            getActiveMethodView: function() {
                var methodView;
                if (this.activeMethod() == 'free') {
                    methodView = this.getFreeMethodView();
                } else {
                    methodView = this.getMethodViewByCode(this.activeMethod());
                }
                return methodView;
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
            },
            toggleMethods: function(code, value) {
                var methods = _.union(this.getAdditionalMethods(), this.getRegion('paymentMethods')());
                _.each(methods, function(elem) {
                    if (code != elem.getCode()) {
                        elem.isEnabled(value);
                    }
                });
            },
            enableMethods: function(code) {
                this.toggleMethods(code, true);
            },
            disableMethods: function(code) {
                this.toggleMethods(code, false);
            },
            getAdditionalMethods: function() {
                var methods = [];
                _.each(this.getRegion('beforeMethods')(), function(elem) {
                    methods = _.union(methods, elem.elems());
                });
                _.each(this.getRegion('afterMethods')(), function(elem) {
                    methods = _.union(methods, elem.elems());
                });
                return methods;
            },
            getMethodControlAdditionalClass: function() {
                return this.getAvailableViews().length == 1 ? ' hidden' : '';
            }
        });
    }
);
