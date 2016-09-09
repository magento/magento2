/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, _, Component, Braintree, quote, fullScreenLoader, additionalValidators) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/paypal',
            code: 'braintree_paypal',
            active: false,
            paymentMethodNonce: null,
            grandTotalAmount: null,

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {

                /**
                 * Triggers when widget is loaded
                 * @param {Object} checkout
                 */
                onReady: function (checkout) {
                    Braintree.checkout = checkout;
                    this.enableButton();
                    Braintree.onReady();
                },

                /**
                 * Triggers on payment nonce receive
                 * @param {Object} response
                 */
                onPaymentMethodReceived: function (response) {
                    this.beforePlaceOrder(response);
                }
            },
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            this._super()
                .observe(['active']);

            this.grandTotalAmount = quote.totals()['base_grand_total'];

            quote.totals.subscribe(function () {
                if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    self.grandTotalAmount = quote.totals()['base_grand_total'];
                    self.reInitPayPal();
                }
            });

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
         * Get payment title
         *
         * @returns {String}
         */
        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].title;
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
         * Triggers when payment method change
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                return;
            }

            // need always re-init Braintree with PayPal configuration
            this.reInitPayPal();
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
         * Update quote billing address
         * @param {Object}customer
         * @param {Object}address
         */
        setBillingAddress: function (customer, address) {
            var billingAddress = {
                street: [address.streetAddress],
                city: address.locality,
                regionCode: address.region,
                postcode: address.postalCode,
                countryId: address.countryCodeAlpha2,
                firstname: customer.firstName,
                lastname: customer.lastName,
                telephone: customer.phone
            };

            quote.billingAddress(billingAddress);
        },

        /**
         * Prepare data to place order
         * @param {Object} data
         */
        beforePlaceOrder: function (data) {
            this.setPaymentMethodNonce(data.nonce);

            if (quote.billingAddress() === null && typeof data.details.billingAddress !== 'undefined') {
                this.setBillingAddress(data.details, data.details.billingAddress);
            }
            this.placeOrder();
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            if (Braintree.checkout) {
                Braintree.checkout.teardown(function () {
                    Braintree.checkout = null;
                });
            }

            this.disableButton();
            this.clientConfig.paypal.amount = this.grandTotalAmount;

            Braintree.setConfig(this.clientConfig);
            Braintree.setup();
        },

        /**
         * Triggers when customer click "Continue to PayPal" button
         */
        payWithPayPal: function () {
            if (additionalValidators.validate()) {
                Braintree.checkout.paypal.initAuthFlow();
            }
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
            var totals = quote.totals(),
                config = {};

            config.paypal = {
                container: 'paypal-container',
                singleUse: true,
                headless: true,
                amount: this.grandTotalAmount,
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                enableShippingAddress: true,

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

            config.paypal.shippingAddressOverride = this.getShippingAddress();

            if (this.getMerchantName()) {
                config.paypal.displayName = this.getMerchantName();
            }

            return config;
        },

        /**
         * Get shipping address
         * @returns {Object}
         */
        getShippingAddress: function () {
            var address = quote.shippingAddress();

            if (address.postcode === null) {

                return {};
            }

            return {
                recipientName: address.firstname + ' ' + address.lastname,
                streetAddress: address.street[0],
                locality: address.city,
                countryCodeAlpha2: address.countryId,
                postalCode: address.postcode,
                region: address.regionCode,
                phone: address.telephone,
                editable: this.isAllowOverrideShippingAddress()
            };
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
        },

        /**
         * Disable submit button
         */
        disableButton: function () {
            // stop any previous shown loaders
            fullScreenLoader.stopLoader();
            fullScreenLoader.startLoader();
            $('[data-button="place"]').attr('disabled', 'disabled');
        },

        /**
         * Enable submit button
         */
        enableButton: function () {
            $('[data-button="place"]').removeAttr('disabled');
            fullScreenLoader.stopLoader();
        }
    });
});
