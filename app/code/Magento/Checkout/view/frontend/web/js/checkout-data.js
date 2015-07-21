/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'checkoutData';

    var getData = function () {
        return storage.get(cacheKey)();
    };

    var saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    };

    if ($.isEmptyObject(getData())) {
        var checkoutData = {
            'selectedShippingAddress': null,
            'shippingAddressData' : null,
            'selectedShippingRate' : null,
            'selectedPaymentMethod' : null,
            'billingAddressData' : null
        };
        saveData(checkoutData);
    }

    return {
        setSelectedShippingAddress: function (data) {
            var obj = getData();
            obj.selectedShippingAddress = data;
            saveData(obj);
        },

        getSelectedShippingAddress: function () {
            return getData().selectedShippingAddress;
        },

        setShippingAddressData: function (data) {
            var obj = getData();
            obj.shippingAddressData = data;
            saveData(obj);
        },

        setSelectedShippingRate: function (data) {
            var obj = getData();
            obj.selectedShippingRate = data;
            saveData(obj);
        },

        setSelectedPaymentMethod: function (data) {
            var obj = getData();
            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        setBillingAddressData: function (data) {
            var obj = getData();
            obj.billingAddressData = data;
            saveData(obj);
        }
    }
});
