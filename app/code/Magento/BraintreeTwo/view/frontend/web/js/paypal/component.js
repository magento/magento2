/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'braintree'
    ],
    function (
        $,
        _,
        Component,
        braintree
    ) {
        'use strict';

        var currentIntegration;

        return Component.extend({

            defaults: {

                /**
                 * {String}
                 */
                integration: 'custom',

                /**
                 * {String}
                 */
                clientToken: null,

                /**
                 * {Object}
                 */
                clientConfig: {

                    /**
                     * @param {Object} integration
                     */
                    onReady: function (integration) {
                        $('body').trigger('processStop');
                        currentIntegration = integration;
                        currentIntegration.paypal.initAuthFlow();
                    },

                    /**
                     * @param {Object} payload
                     */
                    onPaymentMethodReceived: function (payload) {
                        console.log(payload);
                    }
                }
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();
                this.initComponent();

                return this;
            },

            /**
             * @returns this
             */
            initComponent: function () {
                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);

                $('body').off('braintreePaypalClick')
                    .on('braintreePaypalClick', this.clickHandler.bind(this));
            },

            /**
             * @param {Object} event
             * @param {Object} data
             */
            clickHandler: function (event, data) {
                event.preventDefault();

                if (currentIntegration) {
                    currentIntegration.teardown(function () {
                        currentIntegration = null;
                        braintree.setup(this.clientToken, this.integration, this.getClientConfig(data));
                    }.bind(this));

                    return;
                }

                braintree.setup(this.clientToken, this.integration, this.getClientConfig(data));
            },

            /**
             * @returns {Object}
             */
            getClientConfig: function (data) {
                this.clientConfig.paypal = {
                    singleUse: true,
                    amount: data.amount,
                    currency: data.currency,
                    locale: data.locale,
                    enableShippingAddress: true,
                    headless: true
                };

                return this.clientConfig;
            }
        });
    }
);
