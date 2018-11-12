/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/create-billing-address',
    'mage/translate'
], function (
    $,
    _,
    wrapper,
    Component,
    Braintree,
    quote,
    fullScreenLoader,
    additionalValidators,
    VaultEnabler,
    createBillingAddress,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/paypal',
            code: 'braintree_paypal',
            active: false,
            paymentMethodNonce: null,
            grandTotalAmount: null,
            isReviewRequired: false,
            customerEmail: null,

            /**
             * Additional payment data
             *
             * {Object}
             */
            additionalData: {},

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {
                dataCollector: {
                    paypal: true
                },

                /**
                 * Triggers when widget is loaded
                 * @param {Object} checkout
                 */
                onReady: function (checkout) {
                    Braintree.checkout = checkout;
                    this.additionalData['device_data'] = checkout.deviceData;
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
                .observe(['active', 'isReviewRequired', 'customerEmail']);

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            this.vaultEnabler.isActivePaymentTokenEnabler.subscribe(function () {
                self.onVaultPaymentTokenEnablerChange();
            });

            this.grandTotalAmount = quote.totals()['base_grand_total'];

            quote.totals.subscribe(function () {
                if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    self.grandTotalAmount = quote.totals()['base_grand_total'];
                }
            });

            // for each component initialization need update property
            this.isReviewRequired(false);
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
                postcode: address.postalCode,
                countryId: address.countryCodeAlpha2,
                email: customer.email,
                firstname: customer.firstName,
                lastname: customer.lastName,
                telephone: customer.phone
            };

            billingAddress['region_code'] = address.region;
            billingAddress = createBillingAddress(billingAddress);
            quote.billingAddress(billingAddress);
        },

        /**
         * Prepare data to place order
         * @param {Object} data
         */
        beforePlaceOrder: function (data) {
            this.setPaymentMethodNonce(data.nonce);

            if ((this.isRequiredBillingAddress() || quote.billingAddress() === null) &&
                typeof data.details.billingAddress !== 'undefined'
            ) {
                this.setBillingAddress(data.details, data.details.billingAddress);
            }

            if (this.isSkipOrderReview()) {
                this.placeOrder();
            } else {
                this.customerEmail(data.details.email);
                this.isReviewRequired(true);
            }
        },

        /**
         * Re-init PayPal Auth Flow
         * @param {Function} callback - Optional callback
         */
        reInitPayPal: function (callback) {
            if (Braintree.checkout) {
                Braintree.checkout.teardown(function () {
                    Braintree.checkout = null;
                });
            }

            this.disableButton();
            this.clientConfig.paypal.amount = this.grandTotalAmount;
            this.clientConfig.paypal.shippingAddressOverride = this.getShippingAddress();

            if (callback) {
                this.clientConfig.onReady = wrapper.wrap(
                    this.clientConfig.onReady,
                    function (original, checkout) {
                        this.clientConfig.onReady = original;
                        original(checkout);
                        callback();
                    }.bind(this)
                );
            }

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
         * Is billing address required from PayPal side
         * @returns {Boolean}
         */
        isRequiredBillingAddress: function () {
            return window.checkoutConfig.payment[this.getCode()].isRequiredBillingAddress;
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var totals = quote.totals(),
                config = {},
                isActiveVaultEnabler = this.isActiveVault();

            config.paypal = {
                container: 'paypal-container',
                singleUse: !isActiveVaultEnabler,
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

            if (_.isNull(address.postcode) || _.isUndefined(address.postcode)) {
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
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * Returns payment acceptance mark image path
         * @returns {String}
         */
        getPaymentAcceptanceMarkSrc: function () {

            return window.checkoutConfig.payment[this.getCode()].paymentAcceptanceMarkSrc;
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        /**
         * Check if need to skip order review
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return window.checkoutConfig.payment[this.getCode()].skipOrderReview;
        },

        /**
         * Checks if vault is active
         * @returns {Boolean}
         */
        isActiveVault: function () {
            return this.vaultEnabler.isVaultEnabled() && this.vaultEnabler.isActivePaymentTokenEnabler();
        },

        /**
         * Re-init PayPal Auth flow to use Vault
         */
        onVaultPaymentTokenEnablerChange: function () {
            this.clientConfig.paypal.singleUse = !this.isActiveVault();
            this.reInitPayPal();
        },

        /**
         * Disable submit button
         */
        disableButton: function () {
            // stop any previous shown loaders
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.startLoader();
            $('[data-button="place"]').attr('disabled', 'disabled');
        },

        /**
         * Enable submit button
         */
        enableButton: function () {
            $('[data-button="place"]').removeAttr('disabled');
            fullScreenLoader.stopLoader();
        },

        /**
         * Triggers when customer click "Continue to PayPal" button
         */
        payWithPayPal: function () {
            this.reInitPayPal(function () {
                if (!additionalValidators.validate()) {
                    return;
                }

                try {
                    Braintree.checkout.paypal.initAuthFlow();
                } catch (e) {
                    this.messageContainer.addErrorMessage({
                        message: $t('Payment ' + this.getTitle() + ' can\'t be initialized.')
                    });
                }
            }.bind(this));
        },

        /**
         * Get button title
         * @returns {String}
         */
        getButtonTitle: function () {
            return this.isSkipOrderReview() ? 'Pay with PayPal' : 'Continue to PayPal';
        },

        /**
         * Get button id
         * @returns {String}
         */
        getButtonId: function () {
            return this.getCode() + (this.isSkipOrderReview() ? '_pay_with' : '_continue_to');
        }
    });
});
