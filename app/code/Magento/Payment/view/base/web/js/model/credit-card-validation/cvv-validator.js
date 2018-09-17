/**
 * Copyright © Magento, Inc. All rights reserved.
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

        /**
         * CVV number validation
         * validate digit count fot CVV code
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
    }
);
