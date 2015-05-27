/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/quote'
    ],
    function (urlBuilder, quote) {
        "use strict";
        var serviceUrl;
        if (quote.getCheckoutMethod()() === 'guest') {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/addresses', {quoteId: quote.getQuoteId()});
        } else {
            serviceUrl =  urlBuilder.createUrl('/carts/mine/addresses', {});
        }
        return {
            getRates: function(address) {
                var shippingRates = [];
                if (this.validate(address)) {

                }
                return shippingRates;
            },

            validate: function(address) {
                //todo implement logic here
                return false
            }
        };
    }
);