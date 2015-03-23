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
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        './select-shipping-address'
    ],
    function(quote, addressList, storage, errorList, selectShippingAddress) {
        "use strict";
        return function(billingAddressId, useForShipping) {
            var billingAddress = addressList.getAddressById(billingAddressId);
            if (!billingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }
            storage.post(
                '/rest/default/V1/carts/' + quote.getQuoteId()  + '/billing-address',
                JSON.stringify({
                    "cartId": quote.getQuoteId(),
                    "address": billingAddress
                })
            ).success(
                function (response) {
                    billingAddress.id = response;
                    if (useForShipping === '1') {
                        selectShippingAddress(billingAddressId, '1');
                    } else {
                        quote.setBillingAddress(billingAddress);
                    }
                }
            ).error(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setBillingAddress(null);
                }
            );
        };
    }
);
