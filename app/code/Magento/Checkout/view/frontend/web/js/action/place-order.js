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
        'Magento_Customer/js/model/customer'
    ],
    function(quote, urlBuilder, storage, url, errorList, customer) {
        "use strict";
        return function() {
            if (quote.getCheckoutMethod()() === 'register') {
                customer.addCustomerAddress(quote.getBillingAddress()());
                customer.addCustomerAddress(quote.getShippingAddress()());
                storage.post(
                    urlBuilder.createUrl('/carts/:quoteId/ordercreatingaccount', {quoteId: quote.getQuoteId()}),
                    JSON.stringify({
                        customer: customer.customerData,
                        password: customer.getDetails('password')
                    })
                ).done(
                    function() {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                ).error(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                    }
                );
            } else {
                storage.put(
                    urlBuilder.createUrl('/carts/:quoteId/order', {quoteId: quote.getQuoteId()})
                ).done(
                    function() {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                ).error(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                    }
                );
            }
        };
    }
);
