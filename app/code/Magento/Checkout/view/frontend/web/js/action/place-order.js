/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Ui/js/model/errorlist'
    ],
    function(quote, urlBuilder, storage, url, errorList) {
        return function() {
            storage.put(
                urlBuilder.createUrl('/carts/:quoteId/order', {quoteId: quote.getQuoteId()})
            ).done(
                function() {
                    window.location.replace(url.build('checkout/onepage/success/'));
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                }
            );
        }
    }
);
