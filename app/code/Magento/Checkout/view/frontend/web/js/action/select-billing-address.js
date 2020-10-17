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
        } else {
            address = billingAddress;
        }

        if (addressesAreEqual(address, quote.billingAddress())) {
            return;
        }

        quote.billingAddress(address);
    };

    function addressesAreEqual (a, b) {
        if (a === b) {
            return true;
        }
        if (a == null || b == null) {
            return false;
        }

        return JSON.stringify(sortAddress(a)) == JSON.stringify(sortAddress(b));
    }

    function sortAddress (address) {
        return Object.keys(address).sort().reduce(function (result, key) {
            result[key] = address[key];
            return result;
        }, {});
    }
});
