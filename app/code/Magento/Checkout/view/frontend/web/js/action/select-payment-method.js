/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        '../model/quote'
    ],
    function(quote) {
        'use strict';
        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);
        }
    }
);
