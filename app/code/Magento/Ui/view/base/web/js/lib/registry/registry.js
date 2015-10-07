/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'underscore',
    './storage',
    './events'
], function (utils, _, Storage, Events) {
    'use strict';

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

    function Registry() {
        this.storage = new Storage();
        this.events = new Events(this.storage);
    }

    Registry.prototype = {
        constructor: Registry,

        /**
         * Retrieves data from registry.
         *
         * @param {(String|Array)} elems -
         *      An array of elements' names or a string of names divided by spaces.
         * @param {Function} [callback] -
         *      Callback function that will be triggered
         *      when all of the elements are registered.
         * @returns {Array|*|Undefined}
         *      Returns either an array of elements
         *      or an element itself if only is requested.
         *      If callback function is specified then returns 'undefined'.
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
         * @returns {registry} Chainable.
         */
        set: function (elem, value) {
            this.storage.set(elem, value);
            this.events.resolve(elem);

            return this;
        },

        /**
         * Removes specified elements from a storage.
         * @param {(String|Array)} elems -
         *      An array of elements' names or a string of names divided by spaces.
         * @returns {registry} Chainable.
         */
        remove: function (elems) {
            elems = utils.stringToArray(elems);

            this.storage.remove(elems);

            return this;
        },

       /**
         * Checks whether specified elements has been registered.
         *
         * @param {(String|Array)} elems -
         *      An array of elements' names or a string of names divided by spaces.
         * @returns {Boolean}
         */
        has: function (elems) {
            elems = utils.stringToArray(elems);

            return this.storage.has(elems);
        },

        async: function (name) {
            return async.bind(null, name, this);
        },

        create: function () {
            return new Registry;
        }
    };

    return new Registry;
});
