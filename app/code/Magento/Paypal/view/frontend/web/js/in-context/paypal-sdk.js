/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var dfd = $.Deferred();

    /**
     * Loads the PayPal SDK object
     * @param {String} paypalUrl - the url of the PayPal SDK
     */
    return function loadPaypalScript(paypalUrl, dataAttributes)
    {
        //configuration for loaded PayPal script
        require.config({
            paths: {
                paypalSdk: paypalUrl
            },
            shim: {
                paypalSdk: {
                    exports: 'paypal'
                }
            },
            onNodeCreated: function (node, config, name) {
                $.each(dataAttributes, function (index, elem) {
                    node.setAttribute(index, elem);
                });
            }
        });

        if (dfd.state() !== 'resolved') {
            require(['paypalSdk'], function (paypalObject) {
                dfd.resolve(paypalObject);
            });
        }

        return dfd.promise();
    };
});
