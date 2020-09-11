/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
define([
    'underscore',
    'jquery',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'domReady!'
], function (_, $, paypalSdk) {
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
            quoteId: clientConfig.quoteId || '',
            customerId: clientConfig.customerId || '',
            'form_key': clientConfig.formKey
        };

        return $.Deferred(function (deferred) {
            clientConfig.rendererComponent.beforeOnAuthorize(deferred.resolve, deferred.reject, actions)
                .then(function () {
                    $.post(clientConfig.onAuthorizeUrl, params).done(function (res) {
                        clientConfig.rendererComponent
                            .afterOnAuthorize(res, deferred.resolve, deferred.reject, actions);
                    }).fail(function (jqXHR, textStatus, err) {
                        clientConfig.rendererComponent.catchOnAuthorize(err, deferred.resolve, deferred.reject);
                    });
                });
        }).promise();
    }

    return function (clientConfig, element) {
        paypalSdk(clientConfig.sdkUrl).done(function (paypal) {
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
                onClick: function () {
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
