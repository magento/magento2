/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define, alert*/
define(
    [
        '../model/quote',
        '../model/addresslist',
        '../model/step-navigator',
        './select-shipping-address'
    ],
    function(quote, addressList, navigator, selectShippingAddress) {
        "use strict";
        return function(billingAddressId, useForShipping) {
            var billingAddress = addressList.getAddressById(billingAddressId);
            if (!billingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }

            quote.setBillingAddress(billingAddress);
            if (useForShipping === '1' && !quote.isVirtual()) {
                selectShippingAddress(billingAddressId, true);
            } else {
                navigator.setCurrent('billingAddress').goNext();
            }
        };
    }
);
