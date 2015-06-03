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
        var actionCallback;
        var result = function(shippingAddress, sameAsBilling, additionalData) {
            var serviceUrl;
            if (quote.getCheckoutMethod()() === 'guest') {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/addresses', {quoteId: quote.getQuoteId()});
            } else {
                serviceUrl =  urlBuilder.createUrl('/carts/mine/addresses', {});
            }

            errorList.clear();
            additionalData = additionalData || {};
            shippingAddress['same_as_billing'] = (sameAsBilling) ? 1 : 0;
            quote.setShippingAddress(shippingAddress);

            storage.post(
                serviceUrl,
                JSON.stringify({
                    shippingAddress: quote.getShippingAddress()(),
                    billingAddress: quote.getBillingAddress()(),
                    additionalData: {extensionAttributes : additionalData},
                    checkoutMethod: quote.getCheckoutMethod()()
                })
            ).done(
                function(result) {
                    shippingService.setShippingRates(result.shipping_methods);
                    paymentService.setPaymentMethods(result.payment_methods);
                    quote.setFormattedBillingAddress(result.formatted_billing_address);
                    quote.setFormattedShippingAddress(result.formatted_shipping_address);
                    quote.setTotals(result.totals);
                    navigator.setCurrent('shippingAddress').goNext();
                    if (typeof actionCallback == 'function') {
                        actionCallback(true);
                    }
                }
            ).fail(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error);
                    quote.setShippingAddress(null);
                    quote.setBillingAddress(null);
                    quote.setFormattedBillingAddress(null);
                    quote.setFormattedShippingAddress(null);
                    if (typeof actionCallback == 'function') {
                        actionCallback(false);
                    }
                }
            );
        };
        result.setActionCallback = function (value) {
            actionCallback = value;
        };
        return result;
    }
);
