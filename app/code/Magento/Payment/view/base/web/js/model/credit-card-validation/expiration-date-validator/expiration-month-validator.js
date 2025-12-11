/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

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

    return function (value) {
        var month,
            monthValid;

        if (value.replace(/\s/g, '') === '' || value === '0') {
            return resultWrapper(false, true);
        }

        if (!/^\d*$/.test(value)) {
            return resultWrapper(false, false);
        }

        if (isNaN(value)) {
            return resultWrapper(false, false);
        }

        month = parseInt(value, 10);
        monthValid = month > 0 && month < 13;

        return resultWrapper(monthValid, monthValid);
    };
});
