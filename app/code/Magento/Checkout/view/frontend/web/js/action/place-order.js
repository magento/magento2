/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Ui/js/model/errorlist',
        'Magento_Customer/js/model/customer',
        'underscore'
    ],
    function(quote, urlBuilder, storage, url, errorList, customer, _) {
        "use strict";
        return function(customParams) {
            var payload;
            customParams = customParams || {};
            if (quote.getCheckoutMethod()() === 'register') {
                payload = _.extend({
                    customer: customer.customerData,
                    password: customer.getDetails('password')
                }, customParams);
                customer.setAddressAsDefaultBilling(customer.addCustomerAddress(quote.getBillingAddress()()));
                customer.setAddressAsDefaultShipping(customer.addCustomerAddress(quote.getShippingAddress()()));
                storage.post(
                    urlBuilder.createUrl('/carts/:quoteId/order-with-registration', {quoteId: quote.getQuoteId()}),
                    JSON.stringify(payload)
                ).done(
                    function() {
                        window.location.href = url.build('checkout/onepage/success/');
                    }
                ).fail(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                    }
                );
            } else {
                /**
                 * Checkout for guest and registered customer.
                 */
                var serviceUrl;
                if (quote.getCheckoutMethod()() === 'guest') {
                    serviceUrl =  urlBuilder.createUrl('/guest-carts/:quoteId/order', {quoteId: quote.getQuoteId()});
                } else {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/order', {});
                }
                payload = customParams;
                storage.put(
                    serviceUrl, JSON.stringify(payload)
                ).done(
                    function() {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                ).fail(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                    }
                );
            }
        };
    }
);
