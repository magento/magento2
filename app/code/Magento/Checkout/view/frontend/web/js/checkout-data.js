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
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
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
        initData = function () {
            return {
                'selectedShippingAddress': null, //Selected shipping address pulled from persistence storage
                'shippingAddressFromData': null, //Shipping address pulled from persistence storage
                'newCustomerShippingAddress': null, //Shipping address pulled from persistence storage for customer
                'selectedShippingRate': null, //Shipping rate pulled from persistence storage
                'selectedPaymentMethod': null, //Payment method pulled from persistence storage
                'selectedBillingAddress': null, //Selected billing address pulled from persistence storage
                'billingAddressFromData': null, //Billing address pulled from persistence storage
                'newCustomerBillingAddress': null //Billing address pulled from persistence storage for new customer
            };
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = $.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey);

                if ($.isEmptyObject(data)) {
                    data = initData();
                    saveData(data);
                }
            }

            return data;
        };

    return {
        /**
         * Setting the selected shipping address pulled from persistence storage
         *
         * @param {Object} data
         */
        setSelectedShippingAddress: function (data) {
            var obj = getData();

            obj.selectedShippingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the selected shipping address from persistence storage
         *
         * @return {*}
         */
        getSelectedShippingAddress: function () {
            return getData().selectedShippingAddress;
        },

        /**
         * Setting the shipping address pulled from persistence storage
         *
         * @param {Object} data
         */
        setShippingAddressFromData: function (data) {
            var obj = getData();

            obj.shippingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Pulling the shipping address from persistence storage
         *
         * @return {*}
         */
        getShippingAddressFromData: function () {
            return getData().shippingAddressFromData;
        },

        /**
         * Setting the shipping address pulled from persistence storage for new customer
         *
         * @param {Object} data
         */
        setNewCustomerShippingAddress: function (data) {
            var obj = getData();

            obj.newCustomerShippingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the shipping address from persistence storage for new customer
         *
         * @return {*}
         */
        getNewCustomerShippingAddress: function () {
            return getData().newCustomerShippingAddress;
        },

        /**
         * Setting the selected shipping rate pulled from persistence storage
         *
         * @param {Object} data
         */
        setSelectedShippingRate: function (data) {
            var obj = getData();

            obj.selectedShippingRate = data;
            saveData(obj);
        },

        /**
         * Pulling the selected shipping rate from local storage
         *
         * @return {*}
         */
        getSelectedShippingRate: function () {
            return getData().selectedShippingRate;
        },

        /**
         * Setting the selected payment method pulled from persistence storage
         *
         * @param {Object} data
         */
        setSelectedPaymentMethod: function (data) {
            var obj = getData();

            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        /**
         * Pulling the payment method from persistence storage
         *
         * @return {*}
         */
        getSelectedPaymentMethod: function () {
            return getData().selectedPaymentMethod;
        },

        /**
         * Setting the selected billing address pulled from persistence storage
         *
         * @param {Object} data
         */
        setSelectedBillingAddress: function (data) {
            var obj = getData();

            obj.selectedBillingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the selected billing address from persistence storage
         *
         * @return {*}
         */
        getSelectedBillingAddress: function () {
            return getData().selectedBillingAddress;
        },

        /**
         * Setting the billing address pulled from persistence storage
         *
         * @param {Object} data
         */
        setBillingAddressFromData: function (data) {
            var obj = getData();

            obj.billingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Pulling the billing address from persistence storage
         *
         * @return {*}
         */
        getBillingAddressFromData: function () {
            return getData().billingAddressFromData;
        },

        /**
         * Setting the billing address pulled from persistence storage for new customer
         *
         * @param {Object} data
         */
        setNewCustomerBillingAddress: function (data) {
            var obj = getData();

            obj.newCustomerBillingAddress = data;
            saveData(obj);
        },

        /**
         * Pulling the billing address from persistence storage for new customer
         *
         * @return {*}
         */
        getNewCustomerBillingAddress: function () {
            return getData().newCustomerBillingAddress;
        },

        /**
         * Pulling the email address from persistence storage
         *
         * @return {*}
         */
        getValidatedEmailValue: function () {
            var obj = getData();

            return obj.validatedEmailValue ? obj.validatedEmailValue : '';
        },

        /**
         * Setting the email address pulled from persistence storage
         *
         * @param {String} email
         */
        setValidatedEmailValue: function (email) {
            var obj = getData();

            obj.validatedEmailValue = email;
            saveData(obj);
        },

        /**
         * Pulling the email input field value from persistence storage
         *
         * @return {*}
         */
        getInputFieldEmailValue: function () {
            var obj = getData();

            return obj.inputFieldEmailValue ? obj.inputFieldEmailValue : '';
        },

        /**
         * Setting the email input field value pulled from persistence storage
         *
         * @param {String} email
         */
        setInputFieldEmailValue: function (email) {
            var obj = getData();

            obj.inputFieldEmailValue = email;
            saveData(obj);
        },

        /**
         * Pulling the checked email value from persistence storage
         *
         * @return {*}
         */
        getCheckedEmailValue: function () {
            var obj = getData();

            return obj.checkedEmailValue ? obj.checkedEmailValue : '';
        },

        /**
         * Setting the checked email value pulled from persistence storage
         *
         * @param {String} email
         */
        setCheckedEmailValue: function (email) {
            var obj = getData();

            obj.checkedEmailValue = email;
            saveData(obj);
        }
    };
});
