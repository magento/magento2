/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function ($,
              quote,
              urlBuilder,
              storage,
              errorProcessor,
              customer,
              fullScreenLoader,
              getPaymentInformationAction
    ) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl,
                payload,
                useForShipping;

            /**
             * Checkout for guest and registered customer.
             */
            useForShipping = $(
                `#billing-address-same-as-shipping-${quote.paymentMethod().method}:checkbox:checked`
            ).length > 0;

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/billing-address', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    address: quote.billingAddress(),
                    useForShipping: useForShipping
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/billing-address', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    address: quote.billingAddress(),
                    useForShipping: useForShipping
                };
            }

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function () {
                    var deferred = $.Deferred();

                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                    });
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
