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
     * @param {Array} dataAttributes - Array of the Attributes for PayPal SDK Script tag
     */
    return function loadPaypalScript(paypalUrl, dataAttributes) {
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
            attributes: {
                'paypalSdk': dataAttributes
            },

            /**
             * Add attributes under Paypal SDK Script tag
             */
            onNodeCreated: function (node, config, name) {
                if (config.attributes && config.attributes[name]) {
                    $.each(dataAttributes, function (index, elem) {
                        node.setAttribute(index, elem);
                    });
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
