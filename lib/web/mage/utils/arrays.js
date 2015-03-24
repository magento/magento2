/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './strings'
], function (_, utils) {
    'use strict';

    return {
       /**
         * Facade method to remove/add value from/to array
         * without creating a new instance.
         *
         * @param {Array} arr - Array to be modified.
         * @param {*} value - Value to add/remove.
         * @param {Boolean} add - Flag that specfies operation.
         * @returns {Utils} Chainable.
         */
        toggle: function (arr, value, add) {
            return add ?
                this.add(arr, value) :
                this.remove(arr, value);
        },

        /**
         * Removes the incoming value from array in case
         * without creating a new instance of it.
         *
         * @param {Array} arr - Array to be modified.
         * @param {*} value - Value to be removed.
         * @returns {Utils} Chainable.
         */
        remove: function (arr, value) {
            var index = arr.indexOf(value);

            if (~index) {
                arr.splice(index, 1);
            }

            return this;
        },

        /**
         * Adds the incoming value to array if
         * it's not alredy present in there.
         *
         * @param {Array} arr - Array to be modifed.
         * @param {...*} Values to be added.
         * @returns {Utils} Chainable.
         */
        add: function (arr) {
            var values = _.toArray(arguments).slice(1);

            values.forEach(function (value) {
                if (!~arr.indexOf(value)) {
                    arr.push(value);
                }
            });

            return this;
        },

        /**
         * Extends an incoming array with a specified ammount of undefined values
         * starting from a specified position.
         *
         * @param {Array} container - Array to be extended.
         * @param {Number} size - Ammount of values to be added.
         * @param {Number} [offset=0] - Position at which to start inserting values.
         * @returns {Array} Modified array.
         */
        reserve: function (container, size, offset) {
            container.splice(offset || 0, 0, new Array(size));

            return _.flatten(container);
        },

        /**
         * Compares multiple arrays without tracking order of their elements.
         *
         * @param {...Array} Multiple arrays to compare.
         * @returns {Bollean} True if arrays are identical to each other.
         */
        identical: function () {
            var arrays = _.toArray(arguments),
                first = arrays.shift();

            return arrays.every(function (arr) {
                return arr.length === first.length &&
                    !_.difference(arr, first).length;
            });
        },

        formatOffset: function(elems, offset) {
            if (utils.isEmpty(offset)) {
                offset = -1;
            }

            offset = +offset;

            if (offset < 0) {
                offset += elems.length + 1;
            }

            return offset;
        }
    };
});
