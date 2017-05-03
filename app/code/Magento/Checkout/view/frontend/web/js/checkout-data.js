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
        checkoutData,

        /**
         * @return {*}
         */
        getData = function () {
            return storage.get(cacheKey)();
        },

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        };

    if ($.isEmptyObject(getData())) {
        checkoutData = {
            'selectedShippingAddress': null, // Selected shipping address pullled from local storage (Persistence)
            'shippingAddressFromData': null, // Shipping address pullled from local storage (Persistence)
            'newCustomerShippingAddress': null, // Shipping address pullled from local storage for new customer (Persistence)
            'selectedShippingRate': null, // Shipping rate pulled from local storage (Persistence)
            'selectedPaymentMethod': null, // Payment method pulled from local storage (Persistence)
            'selectedBillingAddress': null, // Selected billing address pullled from local storage (Persistence)
            'billingAddressFromData': null, // Billing address pullled from local storage (Persistence)
            'newCustomerBillingAddress': null, // Billing address pullled from local storage for new customer (Persistence)
            'validatedEmailValue': null, // Validated email address from local storage (Persistence)
            'inputFieldEmailValue' : null // Email input field value from local storage (Persistence)
        };
        saveData(checkoutData);
    }

    return {
        /**
         * Setting the selected shipping address pulled from local storage
         * 
         * @param {Object} data
         */
        setSelectedShippingAddress: function (data) {
            var obj = getData();

            obj.selectedShippingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the selected shipping address from local storage
         * 
         * @return {*}
         */
        getSelectedShippingAddress: function () {
            return getData().selectedShippingAddress;
        },

        /**
         * Setting the shipping address pulled from local storage
         *
         * @param {Object} data
         */
        setShippingAddressFromData: function (data) {
            var obj = getData();

            obj.shippingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Pulling the shipping address from local storage 
         *
         * @return {*}
         */
        getShippingAddressFromData: function () {
            return getData().shippingAddressFromData;
        },

        /**
         * Setting the shipping address pulled from local storage for new customer
         *
         * @param {Object} data
         */
        setNewCustomerShippingAddress: function (data) {
            var obj = getData();

            obj.newCustomerShippingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the shipping address from local storage for new customer
         *
         * @return {*}
         */
        getNewCustomerShippingAddress: function () {
            return getData().newCustomerShippingAddress;
        },

        /**
         * Setting the selected shipping rate pulled from local storage
         *
         * @param {Object} data
         */
        setSelectedShippingRate: function (data) {
            var obj = getData();

            obj.selectedShippingRate = data;
            saveData(obj);
        },

        /**
         * Pulling the selected shipping rate from local storge
         *
         * @return {*}
         */
        getSelectedShippingRate: function () {
            return getData().selectedShippingRate;
        },

        /**
         * Setting the selected payment method pulled from local storage
         *
         * @param {Object} data
         */
        setSelectedPaymentMethod: function (data) {
            var obj = getData();

            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        /**
         * Pulling the payment method from local storage
         *
         * @return {*}
         */
        getSelectedPaymentMethod: function () {
            return getData().selectedPaymentMethod;
        },

        /**
         * Setting the selected billing address pulled from local storage
         *
         * @param {Object} data
         */
        setSelectedBillingAddress: function (data) {
            var obj = getData();

            obj.selectedBillingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the selected billing address from local storage
         *
         * @return {*}
         */
        getSelectedBillingAddress: function () {
            return getData().selectedBillingAddress;
        },

        /**
         * Setting the billing address pulled from local storage
         *
         * @param {Object} data
         */
        setBillingAddressFromData: function (data) {
            var obj = getData();

            obj.billingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Pulling the billing address from local storage
         * @return {*}
         */
        getBillingAddressFromData: function () {
            return getData().billingAddressFromData;
        },

        /**
         * Setting the billing address pulled from local storage for new customer
         *
         * @param {Object} data
         */
        setNewCustomerBillingAddress: function (data) {
            var obj = getData();

            obj.newCustomerBillingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the billing address from local storage for new customer
         *
         * @return {*}
         */
        getNewCustomerBillingAddress: function () {
            return getData().newCustomerBillingAddress;
        },

        /**
         * Pulling the email address from local storage
         *
         * @return {*}
         */
        getValidatedEmailValue: function () {
            var obj = getData();

            return obj.validatedEmailValue ? obj.validatedEmailValue : '';
        },

        /**
         * Setting the email address pulled from local storage
         *
         * @param {String} email
         */
        setValidatedEmailValue: function (email) {
            var obj = getData();

            obj.validatedEmailValue = email;
            saveData(obj);
        },

        /**
         * Pulling the email input field value from local storage
         *
         * @return {*}
         */
        getInputFieldEmailValue: function () {
            var obj = getData();

            return obj.inputFieldEmailValue ? obj.inputFieldEmailValue : '';
        },

        /**
         * Setting the email input field value pulled from local storage
         *
         * @param {String} email
         */
        setInputFieldEmailValue: function (email) {
            var obj = getData();

            obj.inputFieldEmailValue = email;
            saveData(obj);
        }
    };
});
