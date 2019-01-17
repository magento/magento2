/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'paypalInContextExpressCheckout',
    'Magento_Ui/js/model/messageList',
    'underscore',
    'domReady!'
], function ($, paypal, messageList, _) {
    'use strict';

    /**
     * Returns array of allowed funding
     *
     * @param {Object} config
     * @return {Array}
     */
    function getFunding(config) {
        return config.map(function (name) {
            return paypal.FUNDING[name]
        })
    };

    return function (clientConfig, element) {

        var environment = clientConfig.environment;

        paypal.Button.render({

            env: clientConfig.environment,
            client: {
                [environment]: clientConfig.merchantId
            },
            locale: clientConfig.locale,
            funding: {
                allowed: getFunding(clientConfig.allowedFunding),
                disallowed: getFunding(clientConfig.disallowedFunding)
            },
            style: clientConfig.styles,

            // Enable Pay Now checkout flow (optional)
            commit: true,

            validate: function (actions) {
                clientConfig.rendererComponent.initButtonActions(actions)
            },

            /**
             * Execute logic on Paypal button click
             */
            onClick: function () {
                if (typeof clientConfig.onClick === 'function') {
                    clientConfig.onClick();
                }
            },

            /**
             * Set up a payment
             *
             * @return {*}
             */
            payment: function () {
                var params = {
                    quote_id: clientConfig.quoteId,
                    customer_id: clientConfig.customerId || '',
                    button: clientConfig.button,
                    form_key: clientConfig.formKey
                };
                    if (clientConfig.hasOwnProperty('billingAgreement')) {
                        params.paypal_ec_create_ba = clientConfig.billingAgreement;
                    }
                if (!clientConfig.button) {
                    return new paypal.Promise(function (resolve, reject) {
                        clientConfig.additionalAction(clientConfig.messageContainer).done(function () {
                                paypal.request.post(clientConfig.getTokenUrl, params)
                                    .then(function (res) {
                                        if (res.success) {

                                            return resolve(res.token);
                                        } else {
                                            messageList.addErrorMessage({
                                                message: res.error_message
                                            });

                                            return reject(new Error(res.error_message));
                                        }
                                    })
                            }
                        ).fail(
                            function (response) {
                                var error;

                                try {
                                    error = JSON.parse(response.responseText);
                                } catch (exception) {
                                    error = $t('Something went wrong with your request. Please try again later.');
                                }
                                messageList.addErrorMessage({
                                    message: error
                                });

                                return reject(new Error(error));
                            }
                        );
                    });
                } else {
                    return paypal.request.post(clientConfig.getTokenUrl, params)
                        .then(function (res) {
                            if (res.success) {

                                return res.token;
                            } else {
                                messageList.addErrorMessage({
                                    message: res.error_message
                                });
                            }
                        });
                }
            },

            /**
             * Execute the payment
             *
             * @param data
             * @param actions
             * @return {*}
             */
            onAuthorize: function (data, actions) {
                var params =
                    {
                        paymentToken: data.paymentToken,
                        payerId: data.payerID,
                        quoteId: clientConfig.quoteId,
                        method: clientConfig.payment.method,
                        customerId: clientConfig.customerId || '',
                        form_key: clientConfig.formKey
                    };

                return paypal.request.post(clientConfig.onAuthorizeUrl, params)
                    .then(function (res) {
                        if (res.success) {

                            return actions.redirect(window, res.redirectUrl);
                        } else {
                            messageList.addErrorMessage({
                                message: res.error_message
                            });
                        }
                    });

            },

            /**
             * Process cancel action
             *
             * @param data
             * @param actions
             */
            onCancel: function (data, actions) {
                actions.redirect(window, clientConfig.onCancelUrl);
            },

            /**
             * Process errors
             *
             * @param {Object} err
             */
            onError: function (err) {
                // Uncaught error isn't displayed in the console
            }
        }, element);
    };
});
