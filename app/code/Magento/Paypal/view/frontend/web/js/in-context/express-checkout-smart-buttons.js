/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'paypalInContextExpressCheckout',
    'mage/translate'
], function (paypal, $t) {
    'use strict';

    /**
     * Returns array of allowed funding
     *
     * @param {Object} config
     * @return {Array}
     */
    function getFunding(config) {
        return config.map(function (name) {
            return paypal.FUNDING[name];
        });
    }

    return function (clientConfig, element) {
        paypal.Button.render({
            env: clientConfig.environment,
            client: clientConfig.client,
            locale: clientConfig.locale,
            funding: {
                allowed: getFunding(clientConfig.allowedFunding),
                disallowed: getFunding(clientConfig.disallowedFunding)
            },
            style: clientConfig.styles,

            // Enable Pay Now checkout flow (optional)
            commit: clientConfig.commit,

            /**
             * Validate payment method
             *
             * @param {Object} actions
             */
            validate: function (actions) {
                clientConfig.rendererComponent.validate(actions);
            },

            /**
             * Execute logic on Paypal button click
             */
            onClick: function () {
                clientConfig.rendererComponent.onClick();
            },

            /**
             * Set up a payment
             *
             * @return {*}
             */
            payment: function () {
                var params = {
                    'quote_id': clientConfig.quoteId,
                    'customer_id': clientConfig.customerId || '',
                    'form_key': clientConfig.formKey,
                    button: clientConfig.button
                };

                if (!clientConfig.button) {
                    return new paypal.Promise(function (resolve, reject) {
                        clientConfig.additionalAction(clientConfig.messageContainer).done(function () {
                            paypal.request.post(clientConfig.getTokenUrl, params).then(function (res) {
                                if (res.success) {

                                    return resolve(res.token);
                                }

                                clientConfig.rendererComponent.addError(res['error_message']);
                                return reject(new Error(res['error_message']));
                            });
                        }).fail(function (response) {
                            var error;

                            try {
                                error = JSON.parse(response.responseText);
                            } catch (exception) {
                                error = $t('Something went wrong with your request. Please try again later.');
                            }

                            clientConfig.rendererComponent.addError(error);
                            return reject(new Error(error));
                        });
                    });
                }

                return paypal.request.post(clientConfig.getTokenUrl, params).then(function (res) {
                    if (res.success) {
                        return res.token;
                    }
                    clientConfig.rendererComponent.addError(res['error_message']);
                });
            },

            /**
             * Execute the payment
             *
             * @param {Object} data
             * @param {Object} actions
             * @return {*}
             */
            onAuthorize: function (data, actions) {
                var params = {
                    paymentToken: data.paymentToken,
                    payerId: data.payerID,
                    quoteId: clientConfig.quoteId,
                    customerId: clientConfig.customerId || '',
                    'form_key': clientConfig.formKey
                };

                return paypal.request.post(clientConfig.onAuthorizeUrl, params).then(function (res) {
                    if (res.success) {

                        return actions.redirect(window, res.redirectUrl);
                    }
                    clientConfig.rendererComponent.addError(res['error_message']);
                });

            },

            /**
             * Process cancel action
             *
             * @param {Object} data
             * @param {Object} actions
             */
            onCancel: function (data, actions) {
                actions.redirect(window, clientConfig.onCancelUrl);
            },

            /**
             * Process errors
             */
            onError: function () {
                // Uncaught error isn't displayed in the console
            }
        }, element);
    };
});
