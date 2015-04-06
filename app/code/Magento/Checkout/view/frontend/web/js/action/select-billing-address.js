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
        return function(billingAddress, useForShipping) {
            quote.setBillingAddress(billingAddress);
            if (useForShipping === '1' && !quote.isVirtual()) {
                selectShippingAddress(billingAddress, useForShipping);
            } else {
                navigator.setCurrent('billingAddress').goNext();
            }
        };
    }
);
