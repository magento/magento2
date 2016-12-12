/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var globalPriceFormat = {
        requiredPrecision: 2,
        integerRequired: 1,
        decimalSymbol: ',',
        groupSymbol: ',',
        groupLength: ','
    };

    /**
     * Repeats {string} {times} times
     * @param  {String} string
     * @param  {Number} times
     * @return {String}
     */
    function stringPad(string, times) {
        return (new Array(times + 1)).join(string);
    }

    /**
     * Formatter for price amount
     * @param  {Number}  amount
     * @param  {Object}  format
     * @param  {Boolean} isShowSign
     * @return {String}              Formatted value
     */
    function formatPrice(amount, format, isShowSign) {
        var s = '',
            precision, integerRequired, decimalSymbol, groupSymbol, groupLength, pattern, i, pad, j, re, r, am;

        format = _.extend(globalPriceFormat, format);

        // copied from price-option.js | Could be refactored with varien/js.js

        precision = isNaN(format.requiredPrecision = Math.abs(format.requiredPrecision)) ? 2 : format.requiredPrecision;
        integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired;
        decimalSymbol = format.decimalSymbol === undefined ? ',' : format.decimalSymbol;
        groupSymbol = format.groupSymbol === undefined ? '.' : format.groupSymbol;
        groupLength = format.groupLength === undefined ? 3 : format.groupLength;
        pattern = format.pattern || '%s';

        if (isShowSign === undefined || isShowSign === true) {
            s = amount < 0 ? '-' : isShowSign ? '+' : '';
        } else if (isShowSign === false) {
            s = '';
        }
        pattern = pattern.indexOf('{sign}') < 0 ? s + pattern : pattern.replace('{sign}', s);

        // we're avoiding the usage of to fixed, and using round instead with the e representation to address
        // numbers like 1.005 = 1.01. Using ToFixed to only provide trailig zeroes in case we have a whole number
        i = parseInt(
                amount = Number(Math.round(Math.abs(+amount || 0) + 'e+' + precision) + ('e-' + precision)),
                10
            ) + '';
        pad = i.length < integerRequired ? integerRequired - i.length : 0;

        i = stringPad('0', pad) + i;

        j = i.length > groupLength ? i.length % groupLength : 0;
        re = new RegExp('(\\d{' + groupLength + '})(?=\\d)', 'g');

        // replace(/-/, 0) is only for fixing Safari bug which appears
        // when Math.abs(0).toFixed() executed on '0' number.
        // Result is '0.-0' :(

        am = Number(Math.round(Math.abs(amount - i) + 'e+' + precision) + ('e-' + precision));
        r = (j ? i.substr(0, j) + groupSymbol : '') +
            i.substr(j).replace(re, '$1' + groupSymbol) +
            (precision ? decimalSymbol + am.toFixed(2).replace(/-/, 0).slice(2) : '');

        return pattern.replace('%s', r).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    }

    /**
     * Deep clone of Object. Doesn't support functions
     * @param {Object} obj
     * @return {Object}
     */
    function objectDeepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    /**
     * Helper to find ID in name attribute
     * @param   {jQuery} element
     * @returns {undefined|String}
     */
    function findOptionId(element) {
        var re, id, name;

        if (!element) {
            return id;
        }
        name = $(element).attr('name');

        if (name.indexOf('[') !== -1) {
            re = /\[([^\]]+)?\]/;
        } else {
            re = /_([^\]]+)?_/; // just to support file-type-option
        }
        id = re.exec(name) && re.exec(name)[1];

        if (id) {
            return id;
        }
    }

    return {
        formatPrice: formatPrice,
        deepClone: objectDeepClone,
        strPad: stringPad,
        findOptionId: findOptionId
    };
});
