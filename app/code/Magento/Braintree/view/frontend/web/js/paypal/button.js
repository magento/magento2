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
        'braintree',
        'Magento_Braintree/js/paypal/form-builder',
        'domReady!'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        braintree,
        formBuilder
    ) {
        'use strict';

        return Component.extend({

            defaults: {

                integrationName: 'braintreePaypal.currentIntegration',

                /**
                 * {String}
                 */
                displayName: null,

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
                        resolver(function () {
                            registry.set(this.integrationName, integration);
                            $('#' + this.id).removeAttr('disabled');
                        }, this);
                    },

                    /**
                     * @param {Object} payload
                     */
                    onPaymentMethodReceived: function (payload) {
                        $('body').trigger('processStart');

                        formBuilder.build(
                            {
                                action: this.actionSuccess,
                                fields: {
                                    result: JSON.stringify(payload)
                                }
                            }
                        ).submit();
                    }
                }
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super()
                    .initComponent();

                return this;
            },

            /**
             * @returns {Object}
             */
            initComponent: function () {
                var currentIntegration = registry.get(this.integrationName),
                    $this = $('#' + this.id),
                    self = this,
                    data = {
                        amount: $this.data('amount'),
                        locale: $this.data('locale'),
                        currency: $this.data('currency')
                    },
                    initCallback = function () {
                        $this.attr('disabled', 'disabled');
                        registry.remove(this.integrationName);
                        braintree.setup(this.clientToken, 'custom', this.getClientConfig(data));

                        $this.off('click')
                            .on('click', function (event) {
                                event.preventDefault();

                                registry.get(self.integrationName, function (integration) {
                                    try {
                                        integration.paypal.initAuthFlow();
                                    } catch (e) {
                                        $this.attr('disabled', 'disabled');
                                    }
                                });
                            });
                    }.bind(this);

                currentIntegration ?
                    currentIntegration.teardown(initCallback) :
                    initCallback();

                return this;
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

                if (this.displayName) {
                    this.clientConfig.paypal.displayName = this.displayName;
                }

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);

                return this.clientConfig;
            }
        });
    }
);
