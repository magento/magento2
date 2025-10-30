/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([], function () {
    'use strict';

    /**
     * Luhn algorithm verification
     */
    return function (a, b, c, d, e) {
        for (d = +a[b = a.length - 1], e = 0; b--;) {
            c = +a[b];
            d += ++e % 2 ? 2 * c % 10 + (c > 4) : c;
        }

        return !(d % 10);
    };
});
