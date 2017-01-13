/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote'
    ],
    function(quote) {
        'use strict';
        return function(shippingAddress) {
            quote.shippingAddress(shippingAddress);
        };
    }
);
