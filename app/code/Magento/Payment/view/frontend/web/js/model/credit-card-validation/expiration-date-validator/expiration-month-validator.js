/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function () {
        'use strict';

        function result(isValid, isPotentiallyValid) {
            return {
                isValid: isValid,
                isPotentiallyValid: isPotentiallyValid
            };
        }

        return function (value) {
            var month,
                monthValid;

            if ((value.replace(/\s/g, '') === '') || (value === '0')) {
                return result(false, true);
            }

            if (!/^\d*$/.test(value)) {
                return result(false, false);
            }

            if (isNaN(value)) {
                return result(false, false);
            }

            month = parseInt(value, 10);
            monthValid = month > 0 && month < 13;

            return result(monthValid, monthValid);
        };
    }
);
