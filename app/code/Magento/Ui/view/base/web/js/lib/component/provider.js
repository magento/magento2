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
     * Creates observable property using knockouts'
     * 'observableArray' or 'observable' methods,
     * depending on a type of 'value' parameter.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function observable(obj, key, value) {
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        if (_.isFunction(obj[key]) && !ko.isObservable(obj[key])) {
            return;
        }

        if (ko.isObservable(value)) {
            value = value();
        }

        ko.isObservable(obj[key]) ?
            obj[key](value) :
            obj[key] = ko[method](value);
    }

    /**
     * Creates observable propery using 'track' method.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function accessor(obj, key, value) {
        if (_.isFunction(obj[key]) || ko.isObservable(obj[key])) {
            return;
        }

        obj[key] = value;

        if (!ko.es5.isTracked(obj, key)) {
            ko.track(obj, [key]);
        }
    }

    return {

        /**
         * Returns value of the nested property.
         *
         * @param {String} path - Path to the property.
         * @returns {*} Value of the property.
         */
        get: function (path) {
            return utils.nested(this, path);
        },

        /**
         * Sets provided value as a value of the specified nested property.
         * Triggers changes notifications, if value has mutated.
         *
         * @param {String} path - Path to property.
         * @param {*} value - New value of the property.
         * @returns {Component} Chainable.
         */
        set: function (path, value) {
            var data = utils.nested(this, path),
                diffs;

            if (!_.isFunction(data)) {
                diffs = utils.compare(data, value, path);

                utils.nested(this, path, value);

                this._notify(diffs);
            } else {
                utils.nested(this, path, value);
            }

            return this;
        },

        /**
         * Removes nested property from the object.
         *
         * @param {String} path - Path to the property.
         * @returns {Component} Chainable.
         */
        remove: function (path) {
            var data,
                diffs;

            if (!path) {
                return this;
            }

            data = utils.nested(this, path);

            if (!_.isUndefined(data) && !_.isFunction(data)) {
                diffs = utils.compare(data, undefined, path);

                utils.nestedRemove(this, path);

                this._notify(diffs);
            }

            return this;
        },

        /**
         * Creates observable properties for the current object.
         *
         * If 'useTrack' flag is set to 'true' then each property will be
         * created with a ES5 get/set accessor descriptors, instead of
         * making them an observable functions.
         * See 'knockout-es5' library for more information.
         *
         * @param {Boolean} [useAccessors=false] - Whether to create an
         *      observable function or to use property accesessors.
         * @param {(Object|String|Array)} properties - List of observable properties.
         * @returns {Component} Chainable.
         *
         * @example Sample declaration and equivalent knockout methods.
         *      this.key = 'value';
         *      this.array = ['value'];
         *
         *      this.observe(['key', 'array']);
         *      =>
         *          this.key = ko.observable('value');
         *          this.array = ko.observableArray(['value']);
         *
         * @example Another syntaxes of the previous example.
         *      this.observe({
         *          key: 'value',
         *          array: ['value']
         *      });
         */
        observe: function (useAccessors, properties) {
            var model = this,
                trackMethod;

            if (typeof useAccessors !== 'boolean') {
                properties   = useAccessors;
                useAccessors = false;
            }

            trackMethod = useAccessors ? accessor : observable;

            if (_.isString(properties)) {
                properties = properties.split(' ');
            }

            if (Array.isArray(properties)) {
                properties.forEach(function (key) {
                    trackMethod(model, key, model[key]);
                });
            } else if (typeof properties === 'object') {
                _.each(properties, function (value, key) {
                    trackMethod(model, key, value);
                });
            }

            return this;
        },

        /**
         * Delegates call to 'observe' method but
         * with a predefined 'useAccessors' flag.
         *
         * @param {(String|Array|Object)} properties - List of observable properties.
         * @returns {Component} Chainable.
         */
        track: function (properties) {
            this.observe(true, properties);

            return this;
        },

        /**
         *
         */
        _notify: function (diffs) {
            diffs.changes.forEach(function (change) {
                this.trigger(change.path, change.value, change);
            }, this);

            _.each(diffs.containers, function (changes, name) {
                var value = utils.nested(this, name);

                this.trigger(name, value, changes);
            }, this);
        },

        /**
         *
         */
        restore: function () {
            var ns = this.storageConfig.namespace,
                storage = this.storage();

            if (storage) {
                utils.extend(this, storage.get(ns));
            }

            return this;
        },

        /**
         * Stores value of the specified property in components' storage module.
         *
         * @param {String} property
         * @param {*} [data=this[property]]
         * @returns {Component} Chainable.
         */
        store: function (property, data) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            data = data || this.get(property);

            this.storage('set', path, data);

            return this;
        },

        /**
         * Removes stored property.
         *
         * @param {String} property - Property to be removed from storage.
         * @returns {Component} Chainable.
         */
        removeStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            this.storage('remove', path);

            return this;
        }
    };
});
