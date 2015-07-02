/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var billingAddress = ko.observable(null);
        var shippingAddress = ko.observable(null);
        var shippingMethod = ko.observable(null);
        var paymentMethod = ko.observable(null);
        var quoteData = window.checkoutConfig.quoteData;
        var basePriceFormat = window.checkoutConfig.basePriceFormat;
        var priceFormat = window.checkoutConfig.priceFormat;
        var storeCode = window.checkoutConfig.storeCode;
        var totalsData = window.checkoutConfig.totalsData;
        var totals = ko.observable(totalsData);
        var shippingCustomOptions = ko.observable(null);
        var formattedShippingAddress = ko.observable(null);
        var formattedBillingAddress = ko.observable(null);
        var collectedTotals = ko.observable({});
        return {
            totals: totals,
            shippingAddress: shippingAddress,
            shippingMethod: shippingMethod,
            billingAddress: billingAddress,
            paymentMethod: paymentMethod,
            guestEmail: null,

            getQuoteId: function() {
                return quoteData.entity_id;
            },
            isVirtual: function() {
                return !!Number(quoteData.is_virtual);
            },
            getPriceFormat: function() {
                return priceFormat;
            },
            getBasePriceFormat: function() {
                return basePriceFormat;
            },
            getItems: function() {
                return window.checkoutConfig.quoteItemData;
            },
            getTotals: function() {
                return totals;
            },
            setTotals: function(totalsData) {
                if (_.isObject(totalsData.extension_attributes)) {
                    _.each(totalsData.extension_attributes, function(element, index) {
                        totalsData[index] = element;
                    });
                }
                totals(totalsData);
                this.setCollectedTotals('subtotal_with_discount', parseFloat(totalsData.subtotal_with_discount));
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
            getStoreCode: function() {
                return storeCode;
            },
            setShippingCustomOptions: function(customOptions) {
                shippingCustomOptions(customOptions);
            },
            getShippingCustomOptions: function() {
                return shippingCustomOptions;
            },
            setCollectedTotals: function(code, value) {
                var totals = collectedTotals();
                totals[code] = value;
                collectedTotals(totals);
            },
            getCalculatedTotal: function() {
                var total = 0.;
                _.each(collectedTotals(), function(value) {
                    total += value;
                });
                return total;
            }
        };
    }
);
