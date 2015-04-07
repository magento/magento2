/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        '../model/quote',
        '../model/addresslist',
        '../model/url-builder',
        '../model/step-navigator',
        '../model/shipping-service',
        '../model/payment-service',
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function(quote, addressList, urlBuilder, navigator, shippingService, paymentService, storage, errorList) {
        "use strict";
        return function(shippingAddress, sameAsBilling) {
            errorList.clear();
            shippingAddress.sameAsBilling = sameAsBilling;
            quote.setShippingAddress(shippingAddress);
            storage.post(
                urlBuilder.createUrl('/carts/:quoteId/addresses', {quoteId: quote.getQuoteId()}),
                JSON.stringify({
                    shippingAddress: quote.getShippingAddress()(),
                    billingAddress: quote.getBillingAddress()()
                })
            ).done(
                function(result) {
                    shippingService.setShippingRates(result.shipping_methods);
                    paymentService.setPaymentMethods(result.payment_methods);
                    navigator.setCurrent('shippingAddress').goNext();
                }
            ).fail(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error);
                    quote.setShippingAddress(null);
                    quote.setBillingAddress(null);
                }
            );
        };
    }
);
