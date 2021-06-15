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
        'Magento_Checkout/js/action/get-payment-information',
        'underscore'
    ],
    function ($,
              quote,
              urlBuilder,
              storage,
              errorProcessor,
              customer,
              fullScreenLoader,
              getPaymentInformationAction,
              _
    ) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl,
                payload,
                billingAddress = _.omit(quote.billingAddress(), 'hash'),
                cartId = quote.getQuoteId();


            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/billing-address', {
                    cartId: cartId
                });
                payload = {
                    cartId: cartId,
                    address: billingAddress
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/billing-address', {});
                payload = {
                    cartId: cartId,
                    address: billingAddress
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
