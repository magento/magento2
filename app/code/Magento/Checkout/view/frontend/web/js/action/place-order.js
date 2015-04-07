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
        'Magento_Ui/js/model/errorlist'
    ],
    function(quote, urlBuilder, storage, url, errorList) {
        "use strict";
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
                    errorList.add(error);
                }
            );
        };
    }
);
