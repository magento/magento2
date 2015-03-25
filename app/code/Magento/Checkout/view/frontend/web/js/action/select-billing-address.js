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
        '../model/url-builder',
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        './select-shipping-address'
    ],
    function(quote, addressList, navigator, urlBuilder, storage, errorList, selectShippingAddress) {
        "use strict";
        return function(billingAddressId, useForShipping) {
            var billingAddress = addressList.getAddressById(billingAddressId);
            if (!billingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }

            storage.post(
                urlBuilder.createUrl('/carts/:quoteId/billing-address', {quoteId: quote.getQuoteId()}),
                JSON.stringify({
                    "cartId": quote.getQuoteId(),
                    "address": billingAddress,
                    "useForShipping": useForShipping
                })
            ).success(
                function (response) {
                    billingAddress.id = response;
                    quote.setBillingAddress(billingAddress, useForShipping);
                    if (useForShipping === '1' && !quote.isVirtual()) {
                        //TODO: need to use use_for_shipping key in saveBilling request instead additional request
                        quote.setShippingAddress(1);
                        navigator.setCurrent('shippingAddress').goNext();
                        //selectShippingAddress(billingAddressId, true);
                    } else {
                        navigator.setCurrent('billingAddress').goNext();
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
