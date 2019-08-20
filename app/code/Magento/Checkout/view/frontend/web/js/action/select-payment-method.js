/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'mage/storage',
    './get-totals',
    '../model/quote',
    '../model/full-screen-loader',
    '../model/url-builder',
    'Magento_Customer/js/model/customer',
], function (storage, getTotalsAction, quote, fullScreenLoader, urlBuilder, customer) {
    'use strict';

    return function (paymentMethod) {
        var serviceUrl, payload;

        quote.paymentMethod(paymentMethod);

        delete paymentMethod.title;

        payload = {
            method: paymentMethod
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/selected-payment-method', {
                cartId: quote.getQuoteId()
            });
        }
        fullScreenLoader.startLoader();
        storage.put(
            serviceUrl,
            JSON.stringify(payload)
        ).success(function () {
            getTotalsAction([]);
            fullScreenLoader.stopLoader();
        });
    };
});
