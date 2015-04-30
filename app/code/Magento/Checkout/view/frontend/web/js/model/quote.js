/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko'],
    function(ko) {
        var billingAddress = ko.observable(null);
        var shippingAddress = ko.observable(null);
        var shippingMethod = ko.observable(null);
        var paymentMethod = ko.observable(null);
        var paymentData = ko.observable(null);
        var quoteData = window.checkoutConfig.quoteData;
        var currencySymbol = window.checkoutConfig.currencySymbol;
        var baseCurrencySymbol = window.checkoutConfig.baseCurrencySymbol;
        var selectedShippingMethod = ko.observable(window.checkoutConfig.selectedShippingMethod);
        var storeCode = window.checkoutConfig.storeCode;
        var totals = ko.observable({});
        var checkoutMethod = ko.observable(null);
        var shippingCustomOptions = ko.observable(null);
        return {
            getQuoteId: function() {
                return quoteData.entity_id;
            },
            isVirtual: function() {
                return !!Number(quoteData.is_virtual);
            },
            getCurrencySymbol: function() {
                return currencySymbol;
            },
            getBaseCurrencySymbol: function() {
                return baseCurrencySymbol;
            },
            getItems: function() {
                return window.checkoutConfig.quoteItemData;
            },
            getTotals: function() {
                return totals
            },
            setTotals: function(totalsData) {
                totals(totalsData);
            },
            setBillingAddress: function (address) {
                billingAddress(address);
            },
            getBillingAddress: function() {
                return billingAddress;
            },
            setShippingAddress: function (address) {
                shippingAddress(address);
            },
            getShippingAddress: function() {
                return shippingAddress;
            },
            setPaymentMethod: function(paymentMethodCode) {
                paymentMethod(paymentMethodCode);
            },
            getPaymentMethod: function() {
                return paymentMethod;
            },
            setPaymentData: function(data) {
                paymentData(data);
            },
            getPaymentData: function() {
                return paymentData;
            },
            setShippingMethod: function(shippingMethodCode) {
                shippingMethod(shippingMethodCode);
            },
            getShippingMethod: function() {
                return shippingMethod;
            },
            getSelectedShippingMethod: function() {
                return selectedShippingMethod;
            },
            setSelectedShippingMethod: function(shippingMethod) {
                selectedShippingMethod(shippingMethod);
            },
            getStoreCode: function() {
                return storeCode;
            },
            getCheckoutMethod: function() {
                return checkoutMethod;
            },
            setCheckoutMethod: function(method) {
                checkoutMethod(method);
            },
            setShippingCustomOptions: function(customOptions) {
                shippingCustomOptions(customOptions);
            },
            getShippingCustomOptions: function() {
                return shippingCustomOptions;
            }
        };
    }
);
