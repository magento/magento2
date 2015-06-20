/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/select-payment-method'
    ],
    function (ko, $, quote, methodList, selectPaymentMethod) {
        'use strict';
        return {
            availablePaymentMethods: ko.observableArray([]),
            selectedPaymentInfo: ko.observableArray([]),
            isFreeAvailable: false,
            setPaymentMethods: function(methods) {
                var self = this,
                    freeMethod = null;
                $.each(methods, function (key, method) {
                    if (method['code'] == 'free') {
                        self.isFreeAvailable = true;
                        freeMethod = method;
                    }
                });

                if (self.isFreeAvailable && freeMethod && quote.totals().grand_total <= 0) {
                    methods.splice(0, methods.length, freeMethod);
                }

                if (methods.length == 1) {
                    selectPaymentMethod(methods[0])
                } else if(quote.paymentMethod()) {
                    var methodIsAvailable = methods.some(function (item) {
                        return (item.code == quote.paymentMethod().method);
                    });
                    //Unset selected payment method if not available
                    if (!methodIsAvailable) {
                        selectPaymentMethod(null);
                    }
                }

                $.each(methods, function (key, method) {
                    if (method['code'] == 'free') {
                        this.isFreeAvailable = true;
                    }
                });
                methodList(methods);
            },
            getAvailablePaymentMethods: function () {
                var methods = [],
                    self = this;
                $.each(methodList(), function (key, method) {
                    if (self.isFreeMethodActive() && (
                        quote.totals().grand_total <= 0 && method['code'] == 'free'
                        || quote.totals().grand_total > 0 && method['code'] != 'free'
                        ) || !self.isFreeMethodActive()
                    ) {
                        methods.push(method);
                    }
                });
                return methods;
            },
            isFreeMethodActive: function () {
                return this.isFreeAvailable;
            },
            setSelectedPaymentInfo: function(data) {
                this.selectedPaymentInfo(data);
            },
            getSelectedPaymentInfo: function () {
                return this.selectedPaymentInfo();
            },
            getTitleByCode: function(code) {
                var methodTitle = '';
                $.each(this.getAvailablePaymentMethods(), function (key, entity) {
                    if (entity['code'] == code) {
                        methodTitle = entity['title'];
                    }
                });
                return methodTitle;
            }
        }
    }
);
