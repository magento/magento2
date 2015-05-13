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
        };
    }
);
