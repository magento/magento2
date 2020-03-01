/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'rjsResolver',
        'uiRegistry',
        'uiComponent',
        'underscore',
        'jquery',
        'braintreeClient',
        'braintreePayPal',
        'braintreePayPalCheckout',
        'Magento_Braintree/js/paypal/form-builder',
        'domReady!'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        braintreeClient,
        braintreePayPal,
        braintreePayPalCheckout,
        formBuilder
    ) {
        'use strict';

        return Component.extend({

            defaults: {
                displayName: null,
                clientToken: null,
                paypalCheckoutInstance: null
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                var self = this;

                self._super();

                braintreeClient.create({
                    authorization: self.clientToken
                })
                    .then(function (clientInstance) {
                        return braintreePayPal.create({
                            client: clientInstance
                        });
                    })
                    .then(function (paypalCheckoutInstance) {
                        self.paypalCheckoutInstance = paypalCheckoutInstance;

                        return self.paypalCheckoutInstance;
                    });

                self.initComponent();

                return this;
            },

            /**
             * @returns {Object}
             */
            initComponent: function () {
                var self = this,
                    selector = '#' + self.id,
                    $this = $(selector),
                    data = {
                        amount: $this.data('amount'),
                        locale: $this.data('locale'),
                        currency: $this.data('currency')
                    };

                $this.html('');
                braintreePayPalCheckout.Button.render({
                    env: self.environment,
                    style: {
                        color: 'blue',
                        shape: 'rect',
                        size: 'medium',
                        label: 'pay',
                        tagline: false
                    },

                    /**
                     * Payment setup
                     */
                    payment: function () {
                        return self.paypalCheckoutInstance.createPayment(self.getClientConfig(data));
                    },

                    /**
                     * Triggers on `onAuthorize` event
                     *
                     * @param {Object} response
                     */
                    onAuthorize: function (response) {
                        return self.paypalCheckoutInstance.tokenizePayment(response)
                            .then(function (payload) {
                                $('body').trigger('processStart');

                                formBuilder.build(
                                    {
                                        action: self.actionSuccess,
                                        fields: {
                                            result: JSON.stringify(payload)
                                        }
                                    }
                                ).submit();
                            });
                    }
                }, selector);

                return this;
            },

            /**
             * @returns {Object}
             * @private
             */
            getClientConfig: function (data) {
                var config = {
                    flow: 'checkout',
                    amount: data.amount,
                    currency: data.currency,
                    locale: data.locale,
                    enableShippingAddress: true
                };

                if (this.displayName) {
                    config.displayName = this.displayName;
                }

                return config;
            }
        });
    }
);
