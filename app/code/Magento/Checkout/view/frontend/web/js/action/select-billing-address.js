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
        './select-shipping-address',
        'uiRegistry'
    ],
    function(quote, addressList, navigator, selectShippingAddress, registry) {
        "use strict";
        return function(billingAddress, useForShipping) {
            quote.setBillingAddress(billingAddress);
            if (useForShipping === '1' && !quote.isVirtual()) {
                // update shipping address data in corresponding provider
                var shippingAddressSource = registry.get('checkoutProvider');
                var shippingAddress = shippingAddressSource.get('shippingAddress');
                for (var property in billingAddress) {
                    if (billingAddress.hasOwnProperty(property)
                        && shippingAddress.hasOwnProperty(property)
                    ) {
                        shippingAddressSource.set('shippingAddress.' + property, billingAddress[property]);
                    }
                }
                selectShippingAddress(billingAddress, useForShipping);
            } else {
                navigator.setCurrent('billingAddress').goNext();
            }
        };
    }
);
