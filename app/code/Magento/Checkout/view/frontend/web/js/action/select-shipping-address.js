/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
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
        return function(shippingAddressId, sameAsBilling) {
            if (!shippingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }
            var shippingAddress = addressList.getAddressById(shippingAddressId);
            shippingAddress.sameAsBilling = sameAsBilling;

            storage.post(
                urlBuilder.createUrl('/carts/:quoteId/addresses', {quoteId: quote.getQuoteId()}),
                JSON.stringify({shippingAddress: shippingAddress, billingAddress: quote.getBillingAddress()()})
            ).done(
                function(result) {
                    quote.setShippingAddress(shippingAddress);
                    shippingService.prepareRates(result.shipping_methods);
                    paymentService.setPaymentMethods(result.payment_methods);
                    navigator.setCurrent('shippingAddress').goNext();
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setShippingAddress(null);
                    quote.setBillingAddress(null);
                }
            );
        }
    }
);
