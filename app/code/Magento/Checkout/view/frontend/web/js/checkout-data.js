/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Checkout adapter for customer data storage
 *
 * @api
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'checkout-data',

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = {
                    'selectedShippingAddress': null,
                    'shippingAddressFromData': null,
                    'newCustomerShippingAddress': null,
                    'selectedShippingRate': null,
                    'selectedPaymentMethod': null,
                    'selectedBillingAddress': null,
                    'billingAddressFormData': null,
                    'newCustomerBillingAddress': null
                };
                saveData(data);
            }

            return data;
        };

    return {
        /**
         * @param {Object} data
         */
        setSelectedShippingAddress: function (data) {
            var obj = getData();

            obj.selectedShippingAddress = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedShippingAddress: function () {
            return getData().selectedShippingAddress;
        },

        /**
         * @param {Object} data
         */
        setShippingAddressFromData: function (data) {
            var obj = getData();

            obj.shippingAddressFromData = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getShippingAddressFromData: function () {
            return getData().shippingAddressFromData;
        },

        /**
         * @param {Object} data
         */
        setNewCustomerShippingAddress: function (data) {
            var obj = getData();

            obj.newCustomerShippingAddress = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getNewCustomerShippingAddress: function () {
            return getData().newCustomerShippingAddress;
        },

        /**
         * @param {Object} data
         */
        setSelectedShippingRate: function (data) {
            var obj = getData();

            obj.selectedShippingRate = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedShippingRate: function () {
            return getData().selectedShippingRate;
        },

        /**
         * @param {Object} data
         */
        setSelectedPaymentMethod: function (data) {
            var obj = getData();

            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedPaymentMethod: function () {
            return getData().selectedPaymentMethod;
        },

        /**
         * @param {Object} data
         */
        setSelectedBillingAddress: function (data) {
            var obj = getData();

            obj.selectedBillingAddress = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getSelectedBillingAddress: function () {
            return getData().selectedBillingAddress;
        },

        /**
         * @param {Object} data
         */
        setBillingAddressFromData: function (data) {
            var obj = getData();

            obj.billingAddressFromData = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getBillingAddressFromData: function () {
            return getData().billingAddressFromData;
        },

        /**
         * @param {Object} data
         */
        setNewCustomerBillingAddress: function (data) {
            var obj = getData();

            obj.newCustomerBillingAddress = data;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getNewCustomerBillingAddress: function () {
            return getData().newCustomerBillingAddress;
        },

        /**
         * @return {*}
         */
        getValidatedEmailValue: function () {
            var obj = getData();

            return obj.validatedEmailValue ? obj.validatedEmailValue : '';
        },

        /**
         * @param {String} email
         */
        setValidatedEmailValue: function (email) {
            var obj = getData();

            obj.validatedEmailValue = email;
            saveData(obj);
        },

        /**
         * @return {*}
         */
        getInputFieldEmailValue: function () {
            var obj = getData();

            return obj.inputFieldEmailValue ? obj.inputFieldEmailValue : '';
        },

        /**
         * @param {String} email
         */
        setInputFieldEmailValue: function (email) {
            var obj = getData();

            obj.inputFieldEmailValue = email;
            saveData(obj);
        }
    };
});
