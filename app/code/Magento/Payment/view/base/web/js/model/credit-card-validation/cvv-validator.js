/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([], function () {
    'use strict';

    /**
     * @param {*} isValid
     * @param {*} isPotentiallyValid
     * @return {Object}
     */
    function resultWrapper(isValid, isPotentiallyValid) {
        return {
            isValid: isValid,
            isPotentiallyValid: isPotentiallyValid
        };
    }

    /**
     * CVV number validation.
     * Validate digit count fot CVV code.
     *
     * @param {*} value
     * @param {Number} maxLength
     * @return {Object}
     */
    return function (value, maxLength) {
        var DEFAULT_LENGTH = 3;

        maxLength = maxLength || DEFAULT_LENGTH;

        if (!/^\d*$/.test(value)) {
            return resultWrapper(false, false);
        }

        if (value.length === maxLength) {
            return resultWrapper(true, true);
        }

        if (value.length < maxLength) {
            return resultWrapper(false, true);
        }

        if (value.length > maxLength) {
            return resultWrapper(false, false);
        }
    };
});
