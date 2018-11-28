/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery'
        ], factory);
    } else {
        factory(root.jQuery);
    }
}(this, function ($, mage) {
    'use strict';

    $.extend(true, $, mage, {
        mage: {
            /**
             * Returns the floating point remainder (modulo) of the division of the arguments
             *
             * @param {Number} dividend
             * @param {Number} divisor
             * @returns {Number}
             */
            getExactDivision: function (dividend, divisor) {
                var divideEpsilon = 1000,
                    epsilon = divisor / divideEpsilon,
                    remainder = dividend % divisor;

                if (Math.abs(remainder - divisor) < epsilon || Math.abs(remainder) < epsilon) {
                    remainder = 0;
                }

                return remainder;
            }
        }
    });
}));
