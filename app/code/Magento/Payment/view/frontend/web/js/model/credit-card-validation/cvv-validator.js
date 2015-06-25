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

        return function(value, maxLength) {
            var DEFAULT_LENGTH = 3;
            maxLength = maxLength || DEFAULT_LENGTH;

            if (!/^\d*$/.test(value)) {
                return result(false, false);
            }
            if (value.length === maxLength) {
                return result(true, true);
            }
            if (value.length < maxLength) {
                return result(false, true);
            }
            if (value.length > maxLength) {
                return result(false, false);
            }

            return result(true, true);
        };
    }
);
