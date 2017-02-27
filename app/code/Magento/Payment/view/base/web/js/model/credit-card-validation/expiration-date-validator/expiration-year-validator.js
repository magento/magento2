/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        var currentYear = new Date().getFullYear(),
            len = value.length,
            valid,
            expMaxLifetime = 19;

        if (value.replace(/\s/g, '') === '') {
            return resultWrapper(false, true);
        }

        if (!/^\d*$/.test(value)) {
            return resultWrapper(false, false);
        }

        if (len !== 4) {
            return resultWrapper(false, true);
        }

        value = parseInt(value, 10);
        valid = value >= currentYear && value <= currentYear + expMaxLifetime;

        return resultWrapper(valid, valid);
    };
});
