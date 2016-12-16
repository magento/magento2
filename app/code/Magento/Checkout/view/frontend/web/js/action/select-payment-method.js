/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    '../model/quote'
], function (quote) {
    'use strict';

    return function (paymentMethod) {
        quote.paymentMethod(paymentMethod);
    };
});
