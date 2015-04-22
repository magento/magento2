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

    function getStored(ns) {
        var stored = localStorage.getItem(ns);

        return !_.isNull(stored) ? JSON.parse(stored) : {};
    }

    function store(ns, property, data) {
        var stored = getStored(ns);

        utils.nested(stored, property, data);

        localStorage.setItem(ns, JSON.stringify(stored));
    }

    var Provider = _.extend({
        /**
         * Initializes DataProvider instance.
         * @param {Object} config - Settings to initialize object with.
         */
        initialize: function (config) {
            _.extend(this, config);

            this.restore();

            return this;
        },

        /**
         * If path specified, returnes this.data[path], else returns this.data
         * @param  {String} path
         * @return {*} this.data[path] or simply this.data
         */
        get: function (path) {
            return utils.nested(this, path);
        },

        /**
         * Sets value property to path and triggers update by path, passing result
         * @param {String|*} path
         * @param {String|*} value
         * @return {Object} reference to instance
         */
        set: function (path, value) {
            var data = utils.nested(this, path),
                diffs = utils.compare(data, value, path);

            utils.nested(this, path, value);

            diffs.changes.forEach(function (change) {
                this.trigger(change.name, change.value, change);
            }, this);

            _.each(diffs.containers, function (changes, name) {
                this.trigger(name, changes);
            }, this);

            return this;
        },

        restore: function () {
            var stored = getStored(this.dataScope);

            utils.extend(this, stored);
        },

        store: function (property, data) {
            if (!data) {
                data = this.get(property);
            } else {
                this.set(property, data);
            }

            store(this.dataScope, property, data);
        },

        remove: function (path) {
            utils.nestedRemove(this, path);
        }
    }, EventsBus);

    return Class.extend(Provider);
});
