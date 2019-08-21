/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (quote, urlBuilder, storage, errorProcessor, customer, getTotalsAction, fullScreenLoader) {
    'use strict';

    return function (paymentMethod) {
        var serviceUrl,
            payload;

        fullScreenLoader.startLoader();

        payload = {
            cartId: quote.getQuoteId(),
            method: paymentMethod
        };

        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/selected-payment-method', {
                cartId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
        }

        return storage.put(
            serviceUrl, JSON.stringify(payload)
        ).always(
            function () {
                getTotalsAction({}, true);
                quote.paymentMethod(paymentMethod);
                fullScreenLoader.stopLoader();
            }
        );
    };
});
