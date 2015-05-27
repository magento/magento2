/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global define,alert*/
define(
    [
        'ko',
        '../model/quote',
        '../model/url-builder',
        'Magento_Ui/js/model/errorlist',
        'mage/storage',
        'underscore'
    ],
    function (ko, quote, urlBuilder, errorList, storage, _) {
        "use strict";
        return function (callbacks) {
            var serviceUrl = '';
            if (quote.getIsCustomerLoggedIn()()) {
                serviceUrl = urlBuilder.createUrl('/carts/mine/totals', {});
            } else {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/totals', {quoteId: quote.getQuoteId()});
            }

            return storage.get(
                serviceUrl
            ).done(
                function (response) {
                    var proceed = true;
                    _.each(callbacks, function(callback) {
                        proceed = proceed && callback();
                    });
                    if (proceed) {
                        quote.setTotals(response);
                    }
                }
            ).error(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error);
                }
            );

        };
    }
);
