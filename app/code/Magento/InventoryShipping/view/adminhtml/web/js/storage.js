/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    return {
        _data: {},

        /**
         * Sets value of the specified item.
         *
         * @param {String} key - Key of the property.
         * @param {*} value - Properties' value.
         */
        setItem: function (key, value) {
            this._data[key] = value;
        },

        /**
         * Retrieves specfied item.
         *
         * @param {String} key - Key of the property to be retrieved.
         */
        getItem: function (key) {
            return this._data[key];
        },

        /**
         * Generate request data
         */
        getRequestData: function () {
            var requestData = [];
            _.each(this._data, function (data, orderItemId) {
                requestData.push({
                    orderItem: orderItemId,
                    sku: data.sku,
                    qty: data.qty
                });
            });
            return requestData;
        },

        /**
         * Removes specfied item.
         *
         * @param {String} key - Key of the property to be removed.
         */
        removeItem: function (key) {
            delete this._data[key];
        },

        /**
         * Removes all items.
         */
        clear: function () {
            this._data = {};
        }
    };
});
