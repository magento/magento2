/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/events'
], function (_, utils, Class, EventsBus) {
    'use strict';

    var Provider = _.extend({
        /**
         * Initializes DataProvider instance.
         * @param {Object} config - Settings to initialize object with.
         */
        initialize: function (config) {
            _.extend(this.data = {}, config);

            return this;
        },

        /**
         * If path specified, returnes this.data[path], else returns this.data
         * @param  {String} path
         * @return {*} this.data[path] or simply this.data
         */
        get: function (path) {
            return utils.nested(this.data, path);
        },

        /**
         * Sets value property to path and triggers update by path, passing result
         * @param {String|*} path
         * @param {String|*} value
         * @return {Object} reference to instance
         */
        set: function (path, value) {
            var data = utils.nested(this.data, path),
                diffs = utils.compare(data, value, path);

            utils.nested(this.data, path, value);

            diffs.changes.forEach(function (change) {
                this.trigger(change.name, change.value, change);
            }, this);

            _.each(diffs.containers, function (changes, name) {
                this.trigger(name, changes);
            }, this);

            return this;
        },

        remove: function (path) {
            utils.nestedRemove(this.data, path);
        }
    }, EventsBus);

    return Class.extend(Provider);
});
