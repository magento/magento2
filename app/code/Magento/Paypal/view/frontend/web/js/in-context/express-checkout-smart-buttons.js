/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'paypalInContextExpressCheckout'
], function (_, paypal) {
    'use strict';

    /**
     * Returns array of allowed funding
     *
     * @param {Object} config
     * @return {Array}
     */
    function getFunding(config) {
        return _.map(config, function (name) {
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

                return new paypal.Promise(function (resolve, reject) {
                    clientConfig.rendererComponent.beforePayment(resolve, reject).then(function () {
                        paypal.request.post(clientConfig.getTokenUrl, params).then(function (res) {
                            return clientConfig.rendererComponent.afterPayment(res, resolve, reject);
                        }).catch(function (err) {
                            return clientConfig.rendererComponent.catchPayment(err, resolve, reject);
                        });
                    });
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
                    quoteId: clientConfig.quoteId || '',
                    customerId: clientConfig.customerId || '',
                    'form_key': clientConfig.formKey
                };

                return new paypal.Promise(function (resolve, reject) {
                    clientConfig.rendererComponent.beforeOnAuthorize(resolve, reject, actions).then(function () {
                        paypal.request.post(clientConfig.onAuthorizeUrl, params).then(function (res) {
                            clientConfig.rendererComponent.afterOnAuthorize(res, resolve, reject, actions);
                        }).catch(function (err) {
                            return clientConfig.rendererComponent.catchOnAuthorize(err, resolve, reject);
                        });
                    });
                });

            },

            /**
             * Process cancel action
             *
             * @param {Object} data
             * @param {Object} actions
             */
            onCancel: function (data, actions) {
                clientConfig.rendererComponent.onCancel(data, actions);
            },

            /**
             * Process errors
             */
            onError: function (err) {
                clientConfig.rendererComponent.onError(err);
            }
        }, element);
    };
});
