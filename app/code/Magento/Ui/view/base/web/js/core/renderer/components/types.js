/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/class'
], function (_, utils, Class) {
    'use strict';

    return Class.extend({
        initialize: function (types) {
            this.types = types || {};

            this.set(this.types);

            return this;
        },

        set: function (types) {
            types = types || [];

            _.each(types, function (data, type) {
                this.types[type] = this.flatten(data);
            }, this);
        },

        get: function (type) {
            return this.types[type] || {};
        },

        flatten: function (data) {
            var extender = data.extends || [],
                result = {};

            extender = utils.stringToArray(extender);

            extender.push(data);

            extender.forEach(function (item) {
                if (_.isString(item)) {
                    item = this.get(item);
                }

                utils.extend(result, item);
            }, this);

            delete result.extends;

            return result;
        }
    });
});
