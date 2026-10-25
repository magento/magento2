/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
     * Validate digit count for CVV code.
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
