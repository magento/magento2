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
        'uiRegistry',
        '../model/url-builder',
        'mage/storage',
        '../model/payment-service'

    ],
    function (quote, addressList, navigator, selectShippingAddress, registry, urlBuilder, storage, paymentService) {
        "use strict";
        return function (billingAddress, useForShipping, additionalData) {
            additionalData = additionalData || {};
            quote.setBillingAddress(billingAddress);
            if (useForShipping === '1' && !quote.isVirtual()) {
                if (!billingAddress.id) {
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
                }
                selectShippingAddress(billingAddress, useForShipping, additionalData);
            } else if (quote.isVirtual()) {
                storage.post(
                    urlBuilder.createUrl('/carts/:quoteId/addresses', {quoteId: quote.getQuoteId()}),
                    JSON.stringify({
                        billingAddress: quote.getBillingAddress()(),
                        additionalData: {extensionAttributes : additionalData}
                    })
                ).done(
                    function (result) {
                        paymentService.setPaymentMethods(result.payment_methods);
                        navigator.setCurrent('billingAddress').goNext();
                    }
                ).fail(
                    function (response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                        quote.setBillingAddress(null);
                    }
                );
            } else {
                navigator.setCurrent('billingAddress').goNext();
            }
        };
    }
);
