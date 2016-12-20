/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return function (value) {
        var month, len;

        if (value.match('/')) {
            value = value.split(/\s*\/\s*/g);

            return {
                month: value[0],
                year: value.slice(1).join()
            };
        }

        len = value[0] === '0' || value.length > 5 || value.length === 4 || value.length === 3 ? 2 : 1;
        month = value.substr(0, len);

        return {
            month: month,
            year: value.substr(month.length, 4)
        };
    };
});
