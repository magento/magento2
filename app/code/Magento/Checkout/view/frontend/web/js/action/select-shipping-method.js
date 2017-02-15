/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    '../model/quote'
], function (quote) {
    'use strict';

    return function (shippingMethod) {
        quote.shippingMethod(shippingMethod);
    };
});
