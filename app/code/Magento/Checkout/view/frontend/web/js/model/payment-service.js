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
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, quote) {
        return {
            paymentMethods: ko.observableArray([]),
            availablePaymentMethods: ko.observableArray([]),
            selectedPaymentData: ko.observableArray(),
            selectedPaymentInfo: ko.observableArray([]),
            isFreeAvailable: false,
            setPaymentMethods: function(methods) {
                $.each(methods, function (key, method) {
                    if (method['code'] == 'free') {
                        this.isFreeAvailable = true;
                    }
                });
                this.paymentMethods(methods);
            },
            getAvailablePaymentMethods: function () {
                var methods = [],
                    self = this;
                $.each(this.paymentMethods(), function (key, method) {
                    if (self.isFreeMethodActive() && (
                        quote.getCalculatedTotal() <= 0 && method['code'] == 'free'
                        || quote.getCalculatedTotal() > 0 && method['code'] != 'free'
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
            setSelectedPaymentData: function(data) {
                this.selectedPaymentData(data);
            },
            getSelectedPaymentData: function () {
                return this.selectedPaymentData();
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
