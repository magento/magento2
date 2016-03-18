/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        '../model/quote'
    ],
    function ($, quote) {
        'use strict';

        return function (billingAddress) {
            var address = null;

            if (quote.shippingAddress() && billingAddress.getCacheKey() == quote.shippingAddress().getCacheKey()) {
                address = $.extend({}, billingAddress);
                address.saveInAddressBook = false;
            } else {
                address = billingAddress;
            }
            quote.billingAddress(address);
        };
    }
);
