/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function() {
        'use strict';

        function result(isValid, isPotentiallyValid) {
            return {
                isValid: isValid,
                isPotentiallyValid: isPotentiallyValid
            };
        }

        return function(value) {
            var currentFirstTwo,
                currentYear = new Date().getFullYear(),
                firstTwo,
                len = value.length,
                twoDigitYear,
                valid,
                maxYear = 19;

            if (value.replace(/\s/g, '') === '') {
                return result(false, true);
            }

            if (!/^\d*$/.test(value)) {
                return result(false, false);
            }

            if (len < 2) {
                return result(false, true);
            }

            if (len === 3) {
                // 20x === 20x
                firstTwo = value.slice(0, 2);
                currentFirstTwo = String(currentYear).slice(0, 2);
                return result(false, firstTwo === currentFirstTwo);
            }

            if (len > 4) {
                return result(false, false);
            }

            value = parseInt(value, 10);
            twoDigitYear = Number(String(currentYear).substr(2, 2));

            if (len === 2) {
                valid = value >= twoDigitYear && value <= twoDigitYear + maxYear;
            } else if (len === 4) {
                valid = value >= currentYear && value <= currentYear + maxYear;
            }

            return result(valid, valid);
        };
    }
);
