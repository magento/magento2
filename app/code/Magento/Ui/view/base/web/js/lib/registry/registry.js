/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'underscore',
    './storage',
    './events'
], function (utils, _, Storage, Events) {
    'use strict';

    /**
     * Wrapper function used for convinient access to elements.
     * See 'async' method for examples of usage and comparison
     * with a regular 'get' method.
     *
     * @param {String} name - Key of the requested element.
     * @param {Registry} registry - Instance of a registry
     *      where to search for the element.
     * @param {(Function|String)} [method] - Optional callback function
     *      or a name of the elements' method which
     *      will be invoked when element is registered.
     * @returns {*}
     */
    function async(name, registry, method) {
        var args = _.toArray(arguments).slice(3);

        if (_.isString(method)) {
            registry.get(name, function (component) {
                component[method].apply(component, args);
            });
        } else if (_.isFunction(method)) {
            registry.get(name, method);
        } else if (!args.length) {
            return registry.get(name);
        }
    }

    /**
     * @constructor
     */
    function Registry() {
        this.storage = new Storage();
        this.events = new Events(this.storage);
    }

    Registry.prototype = {
        constructor: Registry,

        /**
         * Retrieves data from registry.
         *
         * @param {(String|Array)} elems - An array of elements' names or
         *      a string of names divided by spaces.
         * @param {Function} [callback] - Callback function that will be invoked
         *      when all of the requested elements are registered.
         * @returns {Array|*|Undefined} An array of elements
         *      or an element itself if only one was requested.
         */
        get: function (elems, callback) {
            var records;

            elems = utils.stringToArray(elems) || [];

            if (typeof callback !== 'undefined') {
                this.events.wait(elems, callback);
            } else {
                records = this.storage.get(elems);

                return elems.length > 1 ?
                    records :
                    records[0];
            }
        },

        /**
         * Sets data to registry.
         *
         * @param {String} elem - Elements' name.
         * @param {*} value - Value that will be assigned to the element.
         * @returns {Registry} Chainable.
         */
        set: function (elem, value) {
            this.storage.set(elem, value);
            this.events.resolve(elem);

            return this;
        },

        /**
         * Removes specified elements from a storage.
         *
         * @param {(String|Array)} elems - An array of elements' names or
         *      a string of names divided by spaces.
         * @returns {Registry} Chainable.
         */
        remove: function (elems) {
            elems = utils.stringToArray(elems);

            this.storage.remove(elems);

            return this;
        },

        /**
         * Checks whether specified elements has been registered.
         *
         * @param {(String|Array)} elems - An array of elements' names or
         *      a string of names divided by spaces.
         * @returns {Boolean}
         */
        has: function (elems) {
            elems = utils.stringToArray(elems);

            return this.storage.has(elems);
        },

        /**
         * Creates a function wrapper for the specified element,
         * to provide more convinient access.
         *
         * @param {String} name - Name of the element.
         * @returns {Function}
         *
         * @example Comparison with a 'get' method on requesting elements.
         *      var module = registry.async('name');
         *
         *      module();
         *      => registry.get('name');
         *
         * @example Requesting an element with a callback.
         *      module(function (component) {});
         *
         *      => registry.get('name', function (component) {});
         *
         * @example Requesting an element and invoking its' method.
         *      module('trigger', true);
         *
         *      => registry.get('name', function (component) {
         *          component.trigger(true);
         *      });
         */
        async: function (name) {
            return async.bind(null, name, this);
        },

        /**
         * Creates new instance of a Registry.
         *
         * @returns {Registry} New instance.
         */
        create: function () {
            return new Registry;
        }
    };

    return new Registry;
});
