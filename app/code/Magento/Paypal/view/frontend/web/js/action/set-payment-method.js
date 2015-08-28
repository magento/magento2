/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer'
    ],
    function ($, quote, urlBuilder, storage, errorProcessor, customer) {
        'use strict';

        return function () {
            var serviceUrl,
                payload,
                paymentData = quote.paymentMethod();

            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/selected-payment-method', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData
                };
            }
            return storage.put(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function () {
                    $.mage.redirect(window.checkoutConfig.payment.paypalExpress.redirectUrl[quote.paymentMethod().method]);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        };
    }
);
