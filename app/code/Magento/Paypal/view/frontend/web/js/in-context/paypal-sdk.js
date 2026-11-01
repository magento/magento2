/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
        // require.s.contexts._ is an undocumented RequireJS internal with no public API
        // alternative. Captured here to chain any previously registered onNodeCreated
        // handler (e.g. sri.js) so SRI enforcement is not silently disabled.
        var existingOnNodeCreated = require.s &&
            require.s.contexts &&
            require.s.contexts._ &&
            require.s.contexts._.config &&
            require.s.contexts._.config.onNodeCreated;

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
            onNodeCreated: function (node, config, name) {
                if (typeof existingOnNodeCreated === 'function') {
                    existingOnNodeCreated.apply(this, arguments);
                }

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
