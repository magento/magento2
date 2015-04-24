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
        return function(customParams, callback) {
            var payload;
            customParams = customParams || {};
            if (quote.getCheckoutMethod()() === 'register') {
                if (customParams) {
                    payload = _.extend({
                        customer: customer.customerData,
                        password: customer.getDetails('password')
                    }, customParams);
                }
                customer.setAddressAsDefaultBilling(customer.addCustomerAddress(quote.getBillingAddress()()));
                customer.setAddressAsDefaultShipping(customer.addCustomerAddress(quote.getShippingAddress()()));
                storage.post(
                    urlBuilder.createUrl('/carts/:quoteId/order-with-registration', {quoteId: quote.getQuoteId()}),
                    JSON.stringify(payload)
                ).done(
                    function() {
                        if (_.isFunction(callback)) {
                            callback.call();
                        } else {
                            window.location.href = url.build('checkout/onepage/success/');
                        }
                    }
                ).fail(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                    }
                );
            } else {
                if (customParams) {
                    payload = _.extend({
                        customer: customer.customerData,
                        password: customer.getDetails('password')
                    }, customParams);
                }
                storage.put(
                    urlBuilder.createUrl('/carts/:quoteId/order', {quoteId: quote.getQuoteId()}),
                    JSON.stringify(payload)
                ).done(
                    function() {
                        if (_.isFunction(callback)) {
                            callback.call();
                        } else {
                            window.location.replace(url.build('checkout/onepage/success/'));
                        }
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
