/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(function () {
    'use strict';

    var utils = {
        /**
         * Check if string is empty with trim.
         *
         * @param {String} value
         * @return {Boolean}
         */
        isEmpty: function (value) {
            return value === '' || value == null || value.length === 0 || /^\s+$/.test(value);
        },

        /**
         * Check if string is empty no trim.
         *
         * @param {String} value
         * @return {Boolean}
         */
        isEmptyNoTrim: function (value) {
            return value === '' || value == null || value.length === 0;
        },

        /**
         * Checks if {value} is between numbers {from} and {to}.
         *
         * @param {String} value
         * @param {String} from
         * @param {String} to
         * @return {Boolean}
         */
        isBetween: function (value, from, to) {
            return (from === null || from === '' || value >= utils.parseNumber(from)) &&
                   (to === null || to === '' || value <= utils.parseNumber(to));
        },

        /**
         * Parse price string.
         *
         * @param {String} value
         * @return {Number}
         */
        parseNumber: function (value) {
            var isDot, isComa;

            if (typeof value !== 'string') {
                return parseFloat(value);
            }
            isDot = value.indexOf('.');
            isComa = value.indexOf(',');

            if (isDot !== -1 && isComa !== -1) {
                if (isComa > isDot) {
                    value = value.replace('.', '').replace(',', '.');
                } else {
                    value = value.replace(',', '');
                }
            } else if (isComa !== -1) {
                value = value.replace(',', '.');
            }

            return parseFloat(value);
        },

        /**
         * Removes HTML tags and space characters, numbers and punctuation.
         *
         * @param {String} value -  Value being stripped.
         * @return {String}
         */
        stripHtml: function (value) {
            return value.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ')
                .replace(/[0-9.(),;:!?%#$'"_+=\/-]*/g, '');
        }
    };

    return utils;
});
