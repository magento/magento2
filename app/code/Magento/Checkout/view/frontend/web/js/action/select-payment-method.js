/**
 * Copyright © 2015 Magento. All rights reserved.
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
