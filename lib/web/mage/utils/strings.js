/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var jsonRe = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/;

    return {

        /**
         * Attempts to convert string to one of the primitive values,
         * or to parse it as a valid json object.
         *
         * @param {String} str - String to be processed.
         * @returns {*}
         */
        castString: function (str) {
            try {
                str = str === 'true' ? true :
                    str === 'false' ? false :
                    str === 'null' ? null :
                    +str + '' === str ? +str :
                    jsonRe.test(str) ? JSON.parse(str) :
                    str;
            } catch (e) {}

            return str;
        },

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

        /**
         * Adds 'prefix' to the 'part' value if it was provided.
         *
         * @param {String} prefix
         * @param {String} part
         * @returns {String}
         */
        fullPath: function (prefix, part) {
            return prefix ? prefix + '.' + part : part;
        },

        /**
         * Splits incoming string and returns its' part specified by offset.
         *
         * @param {String} parts
         * @param {Number} [offset]
         * @param {String} [delimiter=.]
         * @returns {String}
         */
        getPart: function (parts, offset, delimiter) {
            delimiter = delimiter || '.';
            parts = parts.split(delimiter);
            offset = this.formatOffset(parts, offset);

            parts.splice(offset, 1);

            return parts.join(delimiter) || '';
        }
    };
});
