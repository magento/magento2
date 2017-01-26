/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cart adapter for customer data storage.
 * It is store cart data in customer data(localStorage) without save on server.
 * Adapter created for caching shipping and totals data, that eliminates unneeded requests for calculation.
 */
define([
    'underscore',
    'Magento_Customer/js/customer-data',
    'mageUtils'
], function (_, storage, utils) {
    'use strict';

    var cacheKey = 'cart-data',
        cartData = {
            totals: null,
            address: null,
            cartVersion: null,
            shippingMethodCode: null,
            shippingCarrierCode: null,
            rates: null
        },

        /**
         * Get data from local storage.
         *
         * @param {String} [key]
         * @returns {*}
         */
        getData = function (key) {
            return key ? storage.get(cacheKey)()[key] : storage.get(cacheKey)();
        },

        /**
         * Set data to local storage.
         *
         * @param {Object} checkoutData
         */
        setData = function (checkoutData) {
            storage.set(cacheKey, checkoutData);
        };

    if (_.isEmpty(getData())) {
        setData(utils.copy(cartData));
    }

    /**
     * Provides get/set/isChanged/clear methods for work with cart data.
     * Can be customized via mixin functionality.
     */
    return {
        cartData: cartData,

        /**
         * Array of required address fields
         */
        requiredFields: ['countryId', 'region', 'regionId', 'postcode'],

        /**
         * Get data from customer data.
         * Concatenate provided key with method name and call method if it exist or makes get by key.
         *
         * @param {String} key
         * @return {*}
         */
        get: function (key) {
            var methodName;

            if (key === cacheKey) {
                return getData();
            }

            methodName = '_get' + key.charAt(0).toUpperCase() + key.slice(1);

            if (this[methodName]) {
                return this[methodName]();
            }

            return getData(key);
        },

        /**
         * Set data to customer data.
         * Concatenate provided key with method name and call method if it exist or makes set by key.
         * @example _setCustomAddress method will be called, if it exist.
         *  set('address', customAddressValue)
         * @example Will set value by provided key.
         *  set('rates', ratesToCompare)
         *
         * @param {String} key
         * @param {*} value
         */
        set: function (key, value) {
            var methodName, obj;

            if (key === cacheKey) {
                _.each(value, function (val, k) {
                    this.set(k, val);
                }, this);

                return;
            }

            methodName = '_set' + key.charAt(0).toUpperCase() + key.slice(1);

            if (this[methodName]) {
                this[methodName](value);
            } else {
                obj = getData();
                obj[key] = value;
                setData(obj);
            }
        },

        /**
         * Clear data in cache.
         * Concatenate provided key with method name and call method if it exist or clear by key.
         * @example _clearCustomAddress method will be called, if it exist.
         *  clear('customAddress')
         * @example Will clear data by provided key.
         *  clear('rates')
         *
         * @param {String} key
         */
        clear: function (key) {
            var methodName;

            if (key === cacheKey) {
                setData(this.cartData);

                return;
            }

            methodName = '_clear' + key.charAt(0).toUpperCase() + key.slice(1);

            if (this[methodName]) {
                this[methodName]();
            } else {
                this.set(key, null);
            }
        },

        /**
         * Check if provided data has difference with cached data.
         * Concatenate provided key with method name and call method if it exist or makes strict equality.
         * @example Will call existing _isAddressChanged.
         *  isChanged('address', addressToCompare)
         * @example Will get data by provided key and make strict equality with provided value.
         *  isChanged('rates', ratesToCompare)
         *
         * @param {String} key
         * @param {*} value
         * @return {Boolean}
         */
        isChanged: function (key, value) {
            var methodName = '_is' + key.charAt(0).toUpperCase() + key.slice(1) + 'Changed';

            if (this[methodName]) {
                return this[methodName](value);
            }

            return this.get(key) !== value;
        },

        /**
         * Compare cached address with provided.
         * Custom method for check object equality.
         *
         * @param {Object} address
         * @returns {Boolean}
         */
        _isAddressChanged: function (address) {
            return JSON.stringify(_.pick(this.get('address'), this.requiredFields)) !==
                JSON.stringify(_.pick(address, this.requiredFields));
        }
    };
});
