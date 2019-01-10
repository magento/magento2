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
        var funding = [];
        _.each(config, function (name) {
            funding.push(paypal.FUNDING[name]);
        }, this);
        return funding;
    };

    return function (clientConfig, element) {

        /**
         *
         * @param handler
         */
        function onChangeValidateStatus(handler) {
            _.each(jQuery('#' + clientConfig.rendererComponent.getAgreementId()).find('input'), function (element) {
                element.addEventListener('change', handler);
            }, this);
        }

        /**
         *
         * @return {Boolean}
         */
        function isValid() {
            return clientConfig.validator.validate();

        }

        /**
         *
         * @param actions
         */
        function toggleButtons(actions) {
            var id = '#agreement[1]-error';
            if (isValid()) {
                actions.enable()
            } else {
                actions.disable();
                //hide error message
                jQuery(id).hide();
            }
        }

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

            validate: function (actions) {
                //@todo move outside. Load this logic as composite
                //disable on the first page load
                toggleButtons(actions);
                onChangeValidateStatus(function () {
                    toggleButtons(actions);
                });
            },

            /**
             * Execute logic on Paypal button click
             */
            onClick: function () {
                if (typeof clientConfig.onClick === "function") {
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
                    customer_id: clientConfig.customerId || "",
                    button: clientConfig.button,
                    form_key: clientConfig.formKey
                };

                if (!clientConfig.button) {
                    return new paypal.Promise(function (resolve, reject) {
                        clientConfig.additionalAction(clientConfig.messageContainer).done(function () {
                                paypal.request.post(clientConfig.startUrl, params)
                                    .then(function (res) {
                                        if (res.success) {
                                            return resolve(res.token);
                                        } else {
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
                                return reject(new Error(error));
                            }
                        );
                    });
                } else {
                    return paypal.request.post(clientConfig.startUrl, params)
                        .then(function (res) {
                            if (res.success) {
                                //add logic to process negative cases
                                // 3. Return res.id from the response
                                return res.token;
                            } else {
                                messageList.addErrorMessage({
                                    message: res.error_message
                                });
                            }
                        })
                        .catch(function (err) {
                            throw err;
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
                        if(res.success) {
                            //add logic to process negative cases
                            // 3. Return res.id from the response
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
            }
        }, element);
    };
});
