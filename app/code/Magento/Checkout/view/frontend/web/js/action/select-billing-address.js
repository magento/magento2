/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    '../model/quote'
], function ($, quote) {
    'use strict';

    return function (billingAddress) {
        var address = null;

        if (quote.shippingAddress() && billingAddress.getCacheKey() == //eslint-disable-line eqeqeq
            quote.shippingAddress().getCacheKey()
        ) {
            address = $.extend(true, {}, billingAddress);
            address.saveInAddressBook = null;
            quote.shippingAddress().same_as_billing = 1;
        } else {
            address = billingAddress;
        }
        quote.billingAddress(address);
    };
});
