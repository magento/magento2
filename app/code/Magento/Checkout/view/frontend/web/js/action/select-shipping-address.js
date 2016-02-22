/**
 * Copyright © 2015 Magento. All rights reserved.
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
