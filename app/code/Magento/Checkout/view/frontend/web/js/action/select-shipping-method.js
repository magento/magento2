/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    '../model/quote'
], function (quote) {
    'use strict';

    return function (shippingMethod) {
        quote.shippingMethod(shippingMethod);
    };
});
