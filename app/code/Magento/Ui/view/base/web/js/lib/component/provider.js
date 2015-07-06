/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils'
], function (ko, _, utils) {
    'use strict';

     /**
     * Wrapper for ko.observable and ko.observableArray.
     * Assignes one or another ko property to obj[key]
     * @param  {Object} obj   - object to store property to
     * @param  {String} key   - key
     * @param  {*} value      - initial value of observable
     */
    function observe(obj, key, value) {
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        if (_.isFunction(obj[key]) && !ko.isObservable(obj[key])) {
            return;
        }

        if (ko.isObservable(obj[key])) {
            if (ko.isObservable(value)) {
                value = value();
            }

            obj[key](value);

            return;
        }

        obj[key] = ko[method](value);
    }

    function notify(diffs, data, callback) {
        diffs.changes.forEach(function (change) {
            callback(change.path, change.value, change);
        });

        _.each(diffs.containers, function (changes, name) {
            var value = utils.nested(data, name);

            callback(name, value, changes);
        });
    }

    return {
        /**
         * Retrieves nested data.
         *
         * @param {String} path - Path to the property.
         * @returns {*}
         */
        get: function (path) {
            return utils.nested(this, path);
        },

        /**
         * Sets value property to path and triggers update by path, passing result.
         *
         * @param {String} path
         * @param {*} value
         * @returns {Component} Chainable.
         */
        set: function (path, value) {
            var data = utils.nested(this, path),
                diffs;

            if (typeof data !== 'function') {
                diffs = utils.compare(data, value, path);

                utils.nested(this, path, value);

                notify(diffs, this, this.trigger);
            } else {
                utils.nested(this, path, value);
            }

            return this;
        },

        /**
         * Removes nested data from the object.
         *
         * @param {String} path - Path to the property that should be removed.
         */
        remove: function (path) {
            utils.nestedRemove(this, path);
        },

        /**
         * If 2 params passed, path is considered as key.
         * Else, path is considered as object.
         * Assignes props to this based on incoming params
         * @param  {Object|String} path
         */
        observe: function (path) {
            var type = typeof path;

            if (type === 'string') {
                path = path.split(' ');
            }

            if (Array.isArray(path)) {
                path.forEach(function (key) {
                    observe(this, key, this[key]);
                }, this);
            } else if (type === 'object') {
                _.each(path, function (value, key) {
                    observe(this, key, value);
                }, this);
            }

            return this;
        },

        restore: function () {
            var ns = this.storageConfig.namespace,
                storage = this.storage();

            if (storage) {
                utils.extend(this, storage.get(ns));
            }

            return this;
        },

        store: function (property, data) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            data = data || this.get(property);

            this.storage('set', path, data);

            return this;
        },

        removeStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            this.storage('remove', path);

            return this;
        }
    };
});
