/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Cart adapter for customer data storage
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote'
], function ($, storage, quote) {
    'use strict';

    var cacheKey = 'cart-data',
        cart,
        getData,
        saveData,
        cartData;

    /**
     * Get data from local storage
     * @returns {Object}
     */
    getData = function () {
        return storage.get(cacheKey)();
    };

    /**
     * Save data to local storage
     * @param {Object} checkoutData
     */
    saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    };

    if ($.isEmptyObject(getData())) {
        cartData = {
            'totals': null,
            'address': null,
            'cartVersion': null,
            'shippingMethodCode': null,
            'shippingCarrierCode': null,
            'rates': null
        };
        saveData(cartData);
    }

    cart = storage.get('cart');

    return {
        /**
         * Array of required address fields
         */
        requiredFields: ['countryId', 'region', 'regionId', 'postcode'],

        /**
         * Set totals data to cache
         *
         * @param {Object} data
         */
        setTotalsCache: function (data) {
            var obj = getData();
            obj.totals = data;
            saveData(obj);
        },

        /**
         * Get totals data from cache
         *
         * @returns {null|Object}
         */
        getTotalsCache: function () {
            return getData().totals;
        },

        /**
         * Set address data to cache
         *
         * @param {Object} data
         */
        setAddress: function (data) {
            var obj = getData();
            obj.address = data;
            saveData(obj);
        },

        /**
         * Get address object from cache
         *
         * @returns {Object}
         */
        getAddress: function () {
            return getData().address;
        },

        /**
         * Set cart version to cache
         *
         * @param {Number} version
         */
        setCartVersion: function (version) {
            var obj = getData();
            obj.cartVersion = version;
            saveData(obj);
        },

        /**
         * Get cart version from cache
         *
         * @returns {null|Number}
         */
        getCartVersion: function () {
            return getData().cartVersion;
        },

        /**
         * Set shipping method code to cache
         *
         * @param {String} code
         */
        setShippingMethodCode: function (code) {
            var obj = getData();
            obj.shippingMethodCode = code;
            saveData(obj);
        },

        /**
         * Get shipping method code from cache
         *
         * @returns {null|String}
         */
        getShippingMethodCode: function () {
            return getData().shippingMethodCode;
        },

        /**
         * Set shipping carrier code to cache
         *
         * @param {String} code
         */
        setShippingCarrierCode: function (code) {
            var obj = getData();
            obj.shippingCarrierCode = code;
            saveData(obj);
        },

        /**
         * Get shipping method carrier from cache
         *
         * @returns {null|String}
         */
        getShippingCarrierCode: function () {
            return getData().shippingCarrierCode;
        },

        /**
         * Set shipping rates to cache
         *
         * @param {Object} rates
         */
        setRatesCache: function (rates) {
            var obj = getData();
            obj.rates = rates;
            saveData(obj);
        },

        /**
         * Get shipping rates frm cache
         *
         * @returns {null|Object}
         */
        getRatesCache: function () {
            return getData().rates;
        },

        /**
         * Verify is address changed
         *
         * @param {Object} address
         * @returns {boolean}
         */
        isAddressChanged: function (address) {
            return JSON.stringify(this.getAddress()) != JSON.stringify(_.pick(address, this.requiredFields));
        },

        /**
         * Verify is cart changed
         *
         * @returns {boolean}
         */
        isCartVersionChanged: function () {
            return this.getCartVersion() != cart().data_id;
        },

        /**
         * Verify is shipping method code changed
         *
         * @returns {boolean}
         */
        isShippingMethodCodeChanged: function () {
            return this.getShippingMethodCode() != quote.shippingMethod()['method_code'];
        },

        /**
         * Verify is shipping carrier code changed
         *
         * @returns {boolean}
         */
        isShippingCarrierCodeChanged: function () {
            return this.getShippingCarrierCode() != quote.shippingMethod()['carrier_code'];
        },

        /**
         * Save sever data to local storage
         *
         * @param {Object} address
         * @param {Object} totals
         */
        saveCartDataToCache: function (address, totals) {
            this.setTotalsCache(totals);
            this.setCartVersion(cart().data_id);
            this.setShippingMethodCode(quote.shippingMethod()['method_code']);
            this.setShippingCarrierCode(quote.shippingMethod()['carrier_code']);
            this.setAddress(_.pick(address, this.requiredFields));
        }
    }
});
