/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global define,alert*/
define(
    [
        'jquery',
        '../model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Ui/js/model/messageList',
        'mage/storage'
    ],
    function ($, quote, resourceUrlManager, messageList, storage) {
        "use strict";
        return function (callbacks, deferred) {
            deferred = deferred || $.Deferred();

            return storage.get(
                resourceUrlManager.getUrlForCartTotals(quote)
            ).done(
                function (response) {
                    var proceed = true;
                    $.each(callbacks, function(index, callback) {
                        proceed = proceed && callback();
                    });
                    if (proceed) {
                        quote.setTotals(response);
                        deferred.resolve();
                    }
                }
            ).error(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    messageList.addErrorMessage(error);
                    deferred.reject();
                }
            );

        };
    }
);
