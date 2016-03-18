/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'es6-collections'
], function () {
    'use strict';

    /**
     * @constructor
     */
    function Storage() {
        this.data = new Map();
    }

    Storage.prototype = {
        constructor: Storage,

        /**
         * Retrieves values of the specified elements.
         *
         * @param {Array} elems - An array of elements.
         * @returns {Array} Array of values.
         */
        get: function (elems) {
            var data = this.data;

            elems = elems || [];

            return elems.map(function (elem) {
                return data.get(elem);
            });
        },

        /**
         * Sets key -> value pair.
         *
         * @param {String} elem - Elements' name.
         * @param {*} value - Value of the element.
         * returns {storage} Chainable.
         */
        set: function (elem, value) {
            var data  = this.data;

            data.set(elem, value);

            return this;
        },

        /**
         * Removes specified elements from storage.
         *
         * @param {Array} elems - An array of elements to be removed.
         * returns {storage} Chainable.
         */
        remove: function (elems) {
            var data = this.data;

            elems.forEach(function (elem) {
                data.delete(elem);
            });

            return this;
        },

        /**
         * Checks whether all of the specified elements has been registered.
         *
         * @param {Array} elems - An array of elements.
         * @returns {Boolean}
         */
        has: function (elems) {
            var data = this.data;

            return elems.every(function (elem) {
                return data.has(elem);
            });
        }
    };

    return Storage;
});
