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
    return function loadPaypalScript(paypalUrl) {
        //configuration for loaded PayPal script
        require.config({
            paths: {
                paypalSdk: paypalUrl
            },
            shim: {
                paypalSdk: {
                    exports: 'paypal'
                }
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
