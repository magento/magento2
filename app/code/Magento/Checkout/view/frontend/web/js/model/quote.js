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
        var quoteData = window.checkoutConfig.quoteData;
        var baseCurrencySymbol = window.checkoutConfig.baseCurrencySymbol;
        var priceFormat = window.checkoutConfig.priceFormat;
        var selectedShippingMethod = ko.observable(window.checkoutConfig.selectedShippingMethod);
        var storeCode = window.checkoutConfig.storeCode;
        var totals = ko.observable({});
        var checkoutMethod = ko.observable(null);
        var shippingCustomOptions = ko.observable(null);
        var formattedShippingAddress = ko.observable(null);
        var formattedBillingAddress = ko.observable(null);
        var grandTotal = ko.observable(null);
        var subtotals = ko.observable({});
        return {
            getQuoteId: function() {
                return quoteData.entity_id;
            },
            isVirtual: function() {
                return !!Number(quoteData.is_virtual);
            },
            getPriceFormat: function() {
                return priceFormat;
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
                if (_.isObject(totalsData.extension_attributes)) {
                    _.each(totalsData.extension_attributes, function(element, index) {
                        totalsData[index] = element;
                    });
                    delete totalsData.extension_attributes;
                }
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
            setFormattedBillingAddress: function (address) {
                formattedBillingAddress(address);
            },
            getFormattedBillingAddress: function() {
                return formattedBillingAddress;
            },
            setFormattedShippingAddress: function (address) {
                formattedShippingAddress(address);
            },
            getFormattedShippingAddress: function() {
                return formattedShippingAddress;
            },
            setPaymentMethod: function(paymentMethodCode) {
                paymentMethod(paymentMethodCode);
            },
            getPaymentMethod: function() {
                return paymentMethod;
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
            },
            getGrandTotal: function() {
                return grandTotal;
            },
            setGrandTotal: function(value) {
                grandTotal(parseFloat(value));
            },
            setSubtotal: function(code, value) {
                var totals = subtotals();
                totals[code] = value;
                subtotals(totals);
            },
            getCalculatedTotal: function() {
                var total = grandTotal();
                _.each(subtotals(), function(value) {
                    total += value;
                });
                return total;
            }
        };
    }
);
