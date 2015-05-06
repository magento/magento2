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

        if(_.isFunction(obj[key]) && !ko.isObservable(obj[key])){
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

    function getStored(ns) {
        var stored = localStorage.getItem(ns);

        return !_.isNull(stored) ? JSON.parse(stored) : {};
    }

    function store(ns, property, data) {
        var stored = getStored(ns);

        utils.nested(stored, property, data);

        localStorage.setItem(ns, JSON.stringify(stored));
    }

    function removeStored(ns, property){
        var stored = getStored(ns);

        utils.nestedRemove(stored, property);

        localStorage.setItem(ns, JSON.stringify(stored));
    }

    function notify(diffs, callback) {
        diffs.changes.forEach(function (change) {
            callback(change.path, change.value, change);
        });

        _.each(diffs.containers, function (changes, name) {
            callback(name, changes);
        });
    }

    return {
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

                notify(diffs, this.trigger);
            } else {
                utils.nested(this, path, value);
            }

            return this;
        },

        removeData: function (path) {
            utils.nestedRemove(this, path);
        },

        restore: function () {
            var stored = getStored(this.name);

            utils.extend(this, stored);
        },

        store: function (property, data) {
            data = data || this.get(property);

            store(this.name, property, data);

            return this;
        },

        removeStored: function (property) {
            removeStored(this.name, property);
        }
    };
});
