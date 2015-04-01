/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    return {
        /**
         * Splits string by separator if it's possible,
         * otherwise returns the incoming value.
         *
         * @param {(String|Array|*)} str - String to split.
         * @param {String} [separator=' '] - Seperator based on which to split the string.
         * @returns {Array|*} Splitted string or the incoming value.
         */
        stringToArray: function (str, separator) {
            separator = separator || ' ';

            return typeof str === 'string' ?
                str.split(separator) :
                str;
        },

        /**
         * Converts the incoming string which consists
         * of a specified delimiters into a format commonly used in form elements.
         *
         * @param {String} name - The incoming string.
         * @param {String} [separator='.']
         * @returns {String} Serialized string.
         *
         * @example
         *      utils.serializeName('one.two.three');
         *      => 'one[two][three]';
         */
        serializeName: function (name, separator) {
            var result;

            separator = separator || '.';
            name = name.split(separator);

            result = name.shift();

            name.forEach(function (part) {
                result += '[' + part + ']';
            });

            return result;
        },

        /**
         * Checks wether the incoming value is not empty,
         * e.g. not 'null' or 'undefined'
         *
         * @param {*} value - Value to check.
         * @returns {Boolean}
         */
        isEmpty: function (value) {
            return value === '' || _.isUndefined(value) || _.isNull(value);
        },

        fullPath: function (prefix, part) {
            return prefix ? prefix + '.' + part : part;
        }
    };
});
