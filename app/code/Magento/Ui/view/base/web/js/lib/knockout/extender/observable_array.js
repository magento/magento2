/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    /**
     * Iterator function.
     *
     * @param {String} callback
     * @param {Array} args
     * @param {Object} elem
     * @returns {*}
     */
    function iterator(callback, args, elem) {
        callback = elem[callback];

        if (_.isFunction(callback)) {
            return callback.apply(elem, args);
        }

        return callback;
    }

    /**
     * Wrapper function.
     *
     * @param {String} method
     * @returns {Function}
     */
    function wrapper(method) {
        return function (iteratee) {
            var callback = iteratee,
                elems = this(),
                args = _.toArray(arguments);

            if (_.isString(iteratee)) {
                callback = iterator.bind(null, iteratee, args.slice(1));

                args.unshift(callback);
            }

            args.unshift(elems);

            return _[method].apply(_, args);
        };
    }

    _.extend(ko.observableArray.fn, {
        each: wrapper('each'),

        map: wrapper('map'),

        filter: wrapper('filter'),

        some: wrapper('some'),

        every: wrapper('every'),

        groupBy: wrapper('groupBy'),

        sortBy: wrapper('sortBy'),

        /**
         * Wrapper for underscore findWhere function.
         *
         * @param {Object} properties
         * @return {Object}
         */
        findWhere: function (properties) {
            return _.findWhere(this(), properties);
        },

        /**
         * Wrapper for underscore contains function.
         *
         * @param {Object} value
         * @return {Boolean}
         */
        contains: function (value) {
            return _.contains(this(), value);
        },

        /**
         * Inverse contains call.
         * @return {Boolean}
         */
        hasNo: function () {
            return !this.contains.apply(this, arguments);
        },

        /**
         * Getter for length property.
         * @returns {Number}
         */
        getLength: function () {
            return this().length;
        },

        /**
         * Create object with keys that gets from each object property.
         * @returns {Object}
         */
        indexBy: function (key) {
            return _.indexBy(this(), key);
        },

        /**
         * Returns a copy of the array with all instances of the values removed
         * @return {Array}
         */
        without: function () {
            var args = Array.prototype.slice.call(arguments);

            args.unshift(this());

            return _.without.apply(_, args);
        },

        /**
         * Returns the first element of an array.
         *
         * @return {*}
         */
        first: function () {
            return _.first(this());
        },

        /**
         * Returns the last element of an array
         *
         * @return {*}
         */
        last: function () {
            return _.last(this());
        },

        /**
         * Iterate and pick provided properties.
         *
         * @return {Array}
         */
        pluck: function () {
            var args = Array.prototype.slice.call(arguments);

            args.unshift(this());

            return _.pluck.apply(_, args);
        }
    });
});
