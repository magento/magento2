/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils'
], function (_, utils) {
    'use strict';

    var store = {};

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
        set: function (types) {
            types = types || {};

            utils.extend(store, types);

            _.each(types, function (data, type) {
                store[type] = flatten(data);
            });
        },

        get: function (type) {
            return store[type] || {};
        }
    };
});
