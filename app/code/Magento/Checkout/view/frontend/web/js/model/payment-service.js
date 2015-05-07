/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko', 'jquery'],
    function (ko, $) {
        return {
            availablePaymentMethods: ko.observableArray([]),
            selectedPaymentData: ko.observableArray(),
            selectedPaymentInfo: ko.observableArray([]),
            setPaymentMethods: function(methods) {
                this.availablePaymentMethods(methods);
            },
            getAvailablePaymentMethods: function () {
                return this.availablePaymentMethods;
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
                $.each(this.availablePaymentMethods(), function (key, entity) {
                    if (entity['code'] == code) {
                        methodTitle = entity['title'];
                    }
                });
                return methodTitle;
            }
        }
    }
);
