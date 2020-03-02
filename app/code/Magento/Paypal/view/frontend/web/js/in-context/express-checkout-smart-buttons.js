/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'Magento_Paypal/js/in-context/paypal-sdk',
    'domReady!'
], function (_, $, paypalSdk) {
    'use strict';

    function performCreateOrder(clientConfig)
    {
        var params = {
            'quote_id': clientConfig.quoteId,
            'customer_id': clientConfig.customerId || '',
            'form_key': clientConfig.formKey,
            button: clientConfig.button
        };

        return $.Deferred(function (defer) {
            clientConfig.rendererComponent.beforePayment(defer.resolve, defer.reject).then(function () {
                $.post(clientConfig.getTokenUrl, params).done(function (res) {
                    clientConfig.rendererComponent.afterPayment(res, defer.resolve, defer.reject);
                }).fail(function (jqXHR, textStatus, err) {
                    clientConfig.rendererComponent.catchPayment(err, defer.resolve, defer.reject);
                });
            });
        }).promise();
    }

    function performOnApprove(clientConfig, data, actions)
    {
        var params = {
            paymentToken: data.orderID,
            payerId: data.payerID,
            quoteId: clientConfig.quoteId || '',
            customerId: clientConfig.customerId || '',
            'form_key': clientConfig.formKey
        };

        return $.Deferred(function (defer) {
            clientConfig.rendererComponent.beforeOnAuthorize(defer.resolve, defer.reject, actions).then(function () {
                $.post(clientConfig.onAuthorizeUrl, params).done(function (res) {
                    clientConfig.rendererComponent.afterOnAuthorize(res, defer.resolve, defer.reject, actions);
                }).fail(function (jqXHR, textStatus, err) {
                    clientConfig.rendererComponent.catchOnAuthorize(err, defer.resolve, defer.reject);
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
                createOrder: function () {
                    return performCreateOrder(clientConfig);
                },
                onApprove: function (data, actions) {
                    performOnApprove(clientConfig, data, actions);
                },
                onClick: function () {
                    clientConfig.rendererComponent.validate();
                    clientConfig.rendererComponent.onClick();
                },
                onCancel: function (data, actions) {
                    clientConfig.rendererComponent.onCancel(data, actions);
                },
                onError: function (err) {
                    clientConfig.rendererComponent.onError(err);
                },
            }).render(element);
        });
    };
});
