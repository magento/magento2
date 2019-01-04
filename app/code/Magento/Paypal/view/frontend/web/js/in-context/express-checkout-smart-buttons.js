/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'paypalInContextExpressCheckout',
    'Magento_Ui/js/model/messageList',
    'underscore',
    'domReady!'
], function (paypal, messageList, _) {
    'use strict';

    /**
     * Returns array of allowed funding
     *
     * @param {Object} config
     * @return {Array}
     */
    function getFunding(config) {
        var funding = [];
        _.each(config, function (name) {
            funding.push(paypal.FUNDING[name]);
        }, this);
        return funding;
    };

    return function (clientConfig, element) {
        paypal.Button.render({

            env: clientConfig.environment,
            client: {[clientConfig.environment]:clientConfig.merchantId},
            locale: clientConfig.locale,
            funding: {
                allowed: getFunding(clientConfig.allowedFunding),
                disallowed: getFunding(clientConfig.disallowedFunding)
            },
            style: clientConfig.styles,

            // Enable Pay Now checkout flow (optional)
            commit: true,

            // Set up a payment
            payment: function (data, actions) {
                var params = {
                    quote_id: clientConfig.quoteId,
                    customer_id: clientConfig.customerId || "",
                    button: clientConfig.button,
                    form_key: clientConfig.formKey
                }
                return paypal.request.post(clientConfig.startUrl, params)
                    .then(function (res) {
                        //add logic to process negative cases
                        // 3. Return res.id from the response
                        return res.token;

                });
            },
            // Execute the payment
            onAuthorize: function (data, actions) {
                if (clientConfig.button === 0) {
                    //add logic to set payment method
                }
                var params =
                    {
                        paymentToken: data.paymentToken,
                        payerId: data.payerID,
                        quoteId: clientConfig.quoteId,
                        method: clientConfig.payment.method,
                        customerId: clientConfig.customerId,
                        form_key: clientConfig.formKey
                        // add email for guest customer
                        /*email:*/

                    };
                return paypal.request.post(clientConfig.onAuthorizeUrl, params)
                    .then(function (res) {
                        //add logic to process negative cases
                        // 3. Return res.id from the response
                        return window.location.replace(clientConfig.successUrl);

                    });

            },
            onCancel: function (data, actions) {
                window.alert('Transaction is cancelled.');
            },

            onError: function (err) {
                messageList.addErrorMessage({
                    message: err
                });

            }
        }, element);
    };
});
