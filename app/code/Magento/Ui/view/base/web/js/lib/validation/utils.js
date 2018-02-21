/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    'use strict';

    var utils = {
        /**
         * Check if string is empty with trim
         * @param {string}
            */
        isEmpty: function(value) {
            return (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
        },

        /**
         * Check if string is empty no trim
         * @param {string}
            */
        isEmptyNoTrim: function(value) {
            return (value === '' || (value == null) || (value.length === 0));
        },


        /**
         * Checks if {value} is between numbers {from} and {to}
         * @param {string} value
         * @param {string} from
         * @param {string} to
         * @returns {boolean}
         */
        isBetween: function(value, from, to){
            return (from === null || from === '' || value >= utils.parseNumber(from)) &&
                   (to === null || to === '' || value <= utils.parseNumber(to));
        },

        /**
         * Parse price string
         * @param {string}
            */
        parseNumber: function(value) {
            if (typeof value !== 'string') {
                return parseFloat(value);
            }
            var isDot = value.indexOf('.');
            var isComa = value.indexOf(',');
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
         * @param value Value being stripped.
         * @return {*}
         */
        stripHtml: function(value) {
            return value.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ')
                .replace(/[0-9.(),;:!?%#$'"_+=\/-]*/g, '');
        }
    }

    return utils;
});
