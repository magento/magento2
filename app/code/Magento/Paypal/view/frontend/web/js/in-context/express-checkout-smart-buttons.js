/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
define([
    'underscore',
    'jquery',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function (_, $, paypalSdk, customerData) {
    'use strict';

    /**
     * Triggers beforePayment action on PayPal buttons
     *
     * @param {Object} clientConfig
     * @returns {Object} jQuery promise
     */
    function performCreateOrder(clientConfig) {
        var params = {
            'quote_id': clientConfig.quoteId,
            'customer_id': clientConfig.customerId || '',
            'form_key': clientConfig.formKey,
            button: clientConfig.button
        };

        return $.Deferred(function (deferred) {
            clientConfig.rendererComponent.beforePayment(deferred.resolve, deferred.reject).then(function () {
                $.post(clientConfig.getTokenUrl, params).done(function (res) {
                    clientConfig.rendererComponent.afterPayment(res, deferred.resolve, deferred.reject);
                }).fail(function (jqXHR, textStatus, err) {
                    clientConfig.rendererComponent.catchPayment(err, deferred.resolve, deferred.reject);
                });
            });
        }).promise();
    }

    /**
     * Triggers beforeOnAuthorize action on PayPal buttons
     * @param {Object} clientConfig
     * @param {Object} data
     * @param {Object} actions
     * @returns {Object} jQuery promise
     */
    function performOnApprove(clientConfig, data, actions) {
        var params = {
            paymentToken: data.orderID,
            payerId: data.payerID,
            paypalFundingSource: customerData.get('paypal-funding-source'),
            'form_key': clientConfig.formKey
        };

        return $.Deferred(function (deferred) {
            clientConfig.rendererComponent.beforeOnAuthorize(deferred.resolve, deferred.reject, actions)
                .then(function () {
                    $.post(clientConfig.onAuthorizeUrl, params).done(function (res) {
                        if (res.success === false) {
                            clientConfig.rendererComponent.catchOnAuthorize(res, deferred.resolve, deferred.reject);
                            return;
                        }
                        clientConfig.rendererComponent
                            .afterOnAuthorize(res, deferred.resolve, deferred.reject, actions);
                        customerData.set('paypal-funding-source', '');
                    }).fail(function (jqXHR, textStatus, err) {
                        clientConfig.rendererComponent.catchOnAuthorize(err, deferred.resolve, deferred.reject);
                        customerData.set('paypal-funding-source', '');
                    });
                });
        }).promise();
    }

    return function (clientConfig, element) {
        paypalSdk(clientConfig.sdkUrl, clientConfig.dataAttributes).done(function (paypal) {
            paypal.Buttons({
                style: clientConfig.styles,

                /**
                 * onInit is called when the button first renders
                 * @param {Object} data
                 * @param {Object} actions
                 */
                onInit: function (data, actions) {
                    clientConfig.rendererComponent.validate(actions);
                },

                /**
                 * Triggers beforePayment action on PayPal buttons
                 * @returns {Object} jQuery promise
                 */
                createOrder: function () {
                    return performCreateOrder(clientConfig);
                },

                /**
                 * Triggers beforeOnAuthorize action on PayPal buttons
                 * @param {Object} data
                 * @param {Object} actions
                 */
                onApprove: function (data, actions) {
                    performOnApprove(clientConfig, data, actions);
                },

                /**
                 * Execute logic on Paypal button click
                 */
                onClick: function (data) {
                    customerData.set('paypal-funding-source', data.fundingSource);
                    clientConfig.rendererComponent.validate();
                    clientConfig.rendererComponent.onClick();
                },

                /**
                 * Process cancel action
                 * @param {Object} data
                 * @param {Object} actions
                 */
                onCancel: function (data, actions) {
                    clientConfig.rendererComponent.onCancel(data, actions);
                },

                /**
                 * Process errors
                 *
                 * @param {Error} err
                 */
                onError: function (err) {
                    clientConfig.rendererComponent.onError(err);
                }
            }).render(element);
        });
    };
});
