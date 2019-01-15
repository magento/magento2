/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils'
], function (_, utils) {
    'use strict';

    var store = {};

    /**
     * Flatten a nested data.
     *
     * @param {Object} data
     * @returns {Object}
     */
    function flatten(data) {
        var extender = data.extends || [],
            result = {};

        extender = utils.stringToArray(extender);

        extender.push(data);

        extender.forEach(function (item) {
            if (_.isString(item)) {
                item = store[item] || {};
            }

            utils.extend(result, item);
        });

        delete result.extends;

        return result;
    }

    return {
        /**
         * Set types to store object.
         *
         * @param {Object} types
         */
        set: function (types) {
            types = types || {};

            utils.extend(store, types);

            _.each(types, function (data, type) {
                store[type] = flatten(data);
            });
        },

        /**
         * Get type from store object.
         *
         * @param {String} type
         * @returns {*|{}}
         */
        get: function (type) {
            return store[type] || {};
        }
    };
});
