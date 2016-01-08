/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_BraintreeTwo/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote'
], function (_, Component, Braintree, quote) {
    'use strict';

    var checkout;

    return Component.extend({
        defaults: {
            template: 'Magento_BraintreeTwo/payment/paypal',
            code: 'braintreetwo_paypal',
            active: false,
            paymentMethodNonce: null,

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {

                /**
                 * Triggers when widget is loaded
                 * @param {Object} integration
                 */
                onReady: function (integration) {
                    checkout = integration;
                },

                /**
                 * Triggers on payment nonce receive
                 * @param {Object} response
                 */
                onPaymentMethodReceived: function (response) {
                    this.beforePlaceOrder(response);
                }
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe(['active']);

            this.initClientConfig();

            return this;
        },

        /**
         * Get payment name
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function () {
            var active = this.getCode() === this.isChecked();

            this.active(active);

            return active;
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this.clientConfig = _.extend(this.clientConfig, this.getPayPalConfig());

            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);

            Braintree.config = _.extend(Braintree.config, this.clientConfig);
        },

        /**
         * Set payment nonce
         * @param {String} paymentMethodNonce
         */
        setPaymentMethodNonce: function (paymentMethodNonce) {
            this.paymentMethodNonce = paymentMethodNonce;
        },

        /**
         * Prepare data to place order
         * @param {Object} data
         */
        beforePlaceOrder: function (data) {
            console.log(data);

            if (data.type !== 'PayPalAccount') {
                return;
            }
            this.setPaymentMethodNonce(data.nonce);
            this.placeOrder();
        },

        /**
         * Triggers when customer click "Place Order" button
         */
        initFlow: function () {

            checkout.teardown(function () {
                checkout = null;
            });

            /**
             * Re-init on ready event
             * @param {Object} integration
             */
            this.clientConfig.onReady = function (integration) {
                checkout = integration;
                checkout.paypal.initAuthFlow();
            };
            this.clientConfig.paypal.amount = quote.totals()['base_grand_total'];

            // re-init Braintree
            Braintree.setConfig(this.clientConfig);
            Braintree.setup();
        },

        /**
         * Get locale
         * @returns {String}
         */
        getLocale: function () {
            return window.checkoutConfig.payment[this.getCode()].locale;
        },

        /**
         * Is shipping address can be editable on PayPal side
         * @returns {Boolean}
         */
        isAllowOverrideShippingAddress: function () {
            return window.checkoutConfig.payment[this.getCode()].isAllowShippingAddressOverride;
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var address = quote.shippingAddress(),
                totals = quote.totals(),
                config = {};

            config.paypal = {
                container: 'paypal-container',
                singleUse: true,
                headless: true,
                amount: totals['base_grand_total'],
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                enableShippingAddress: true,
                displayName: this.getMerchantName(),
                shippingAddressOverride: {
                    recipientName: address.firstname + ' ' + address.lastname,
                    streetAddress: address.street[0],
                    locality: address.city,
                    countryCodeAlpha2: address.countryId,
                    postalCode: address.postcode,
                    region: address.regionCode,
                    phone: address.telephone,
                    editable: this.isAllowOverrideShippingAddress()
                },

                /**
                 * Triggers on any Braintree error
                 */
                onError: function () {
                    this.paymentMethodNonce = null;
                },

                /**
                 * Triggers if browser doesn't support PayPal Checkout
                 */
                onUnsupported: function () {
                    this.paymentMethodNonce = null;
                }
            };

            return config;
        },

        /**
         * Get merchant name
         * @returns {String}
         */
        getMerchantName: function () {
            return window.checkoutConfig.payment[this.getCode()].merchantName;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };
        },

        /**
         * Returns payment acceptance mark image path
         * @returns {String}
         */
        getPaymentAcceptanceMarkSrc: function () {

            return window.checkoutConfig.payment[this.getCode()].paymentAcceptanceMarkSrc;
        }
    });
});
