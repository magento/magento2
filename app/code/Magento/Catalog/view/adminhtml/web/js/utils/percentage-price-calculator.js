/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['Magento_Ui/js/lib/validation/utils'], function (utils) {
    'use strict';

    /**
     * Calculates the price input value when entered percentage value.
     *
     * @param {String} price
     * @param {String} input
     *
     * @returns {String}
     */
    return function (price, input) {
        var result,
            lastInputSymbol = input.slice(-1),
            inputPercent = input.slice(0, -1),
            parsedPercent = utils.parseNumber(inputPercent),
            parsedPrice = utils.parseNumber(price);

        if (lastInputSymbol !== '%') {
            result = input;
        } else if (
            input === '%' ||
            isNaN(parsedPrice) ||
            parsedPercent != inputPercent || /* eslint eqeqeq:0 */
            isNaN(parsedPercent) ||
            input === ''
        ) {
            result = '';
        } else if (parsedPercent > 100) {
            result = '0.00';
        } else if (lastInputSymbol === '%') {
            result = parsedPrice - parsedPrice * (inputPercent / 100);
            result = result.toFixed(2);
        } else {
            result = input;
        }

        return result;
    };
});
