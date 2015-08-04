/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage'
    ],
    function (quote, urlManager, storage) {
        'use strict';

        return function () {
            var payload = JSON.stringify(quote.shippingMethod());
            console.log(
                urlManager.getUrlForSetShippingMethod(quote),
                payload
            );
        };
    }
);
