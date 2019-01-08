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

        /** track chackbox status change*/
        function onChangeValidateStatus(handler) {
            _.each(jQuery('#' + clientConfig.rendererComponent.getAgreementId()).find('input'), function (element) {
                element.addEventListener('change', handler);
            }, this);
        }

        function isValid() {
            //todo refactor this. add  of validators insead of hardcode
            var count = jQuery('#' + clientConfig.rendererComponent.getAgreementId()).find('input').length;
            return count >= 0 && jQuery('#' + clientConfig.rendererComponent.getAgreementId()).find('input:checkbox:checked').length == count;
        }

        function toggleButtons(actions) {
            isValid() ? actions.enable() : actions.disable();
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

            onClick: function() {
                if(clientConfig.validator.validate()) {
                    clientConfig.rendererComponent.continueToPayPal();
                }
            },

            // Set up a payment
            payment: function (data, actions) {
                var params = {
                    quote_id: clientConfig.quoteId,
                    customer_id: clientConfig.customerId || "",
                    button: clientConfig.button,
                    form_key: clientConfig.formKey
                };

                return paypal.request.post(clientConfig.startUrl, params)
                    .then(function (res) {
                        //add logic to process negative cases
                        // 3. Return res.id from the response
                        return res.token;

                    })
                    .catch(function (err) {
                        throw err;
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
                        customerId: clientConfig.customerId || '',
                        form_key: clientConfig.formKey
                        // add email for guest customer
                        /*email:*/

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
