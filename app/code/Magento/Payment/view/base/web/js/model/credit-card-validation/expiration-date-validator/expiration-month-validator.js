/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function () {
        'use strict';

        /**
         * Validation result wrapper
         * @param {Boolean} isValid
         * @param {Boolean} isPotentiallyValid
         * @returns {Object}
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
    }
);
