/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore'
], function (ko, _) {
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
            if (_.isObject(totalsData) && _.isObject(totalsData.extension_attributes)) {
                _.each(totalsData.extension_attributes, function(element, index) {
                    totalsData[index] = element;
                });
            }
            totals(totalsData);
            this.setCollectedTotals('subtotal_with_discount', parseFloat(totalsData.subtotal_with_discount));
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
});
