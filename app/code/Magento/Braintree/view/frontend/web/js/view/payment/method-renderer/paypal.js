/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Braintree/js/view/payment/adapter',
    'braintreePayPal',
    'braintreePayPalCheckout',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Braintree/js/view/payment/kount',
    'mage/translate',
    'Magento_Ui/js/model/messageList'
], function (
    $,
    _,
    Component,
    BraintreeAdapter,
    BraintreePayPal,
    BraintreePayPalCheckout,
    quote,
    fullScreenLoader,
    additionalValidators,
    VaultEnabler,
    createBillingAddress,
    kount,
    $t,
    globalMessageList
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/paypal',
            code: 'braintree_paypal',
            active: false,
            grandTotalAmount: null,
            isReviewRequired: false,
            paypalCheckoutInstance: null,
            customerEmail: null,
            vaultEnabler: null,
            paymentPayload: {
                nonce: null
            },
            paypalButtonSelector: '[data-container="paypal-button"]',

            /**
             * Additional payment data
             *
             * {Object}
             */
            additionalData: {},

            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Initialize view.
         *
         * @return {exports}
         */
        initialize: function () {
            var self = this;

            self._super();

            BraintreeAdapter.getApiClient().then(function (clientInstance) {
                return BraintreePayPal.create({
                    client: clientInstance
                });
            }).then(function (paypalCheckoutInstance) {
                self.paypalCheckoutInstance = paypalCheckoutInstance;

                return self.paypalCheckoutInstance;
            });

            kount.getDeviceData()
                .then(function (deviceData) {
                    self.additionalData['device_data'] = deviceData;
                });

            // for each component initialization need update property
            this.isReviewRequired(false);

            return self;
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

            quote.shippingAddress.subscribe(function () {
                if (self.isActive()) {
                    self.reInitPayPal();
                }
            });

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
         * Sets payment payload
         *
         * @param {Object} paymentPayload
         * @private
         */
        setPaymentPayload: function (paymentPayload) {
            this.paymentPayload = paymentPayload;
        },

        /**
         * Update quote billing address
         * @param {Object}customer
         * @param {Object}address
         */
        setBillingAddress: function (customer, address) {
            var billingAddress = {
                street: [address.line1],
                city: address.city,
                postcode: address.postalCode,
                countryId: address.countryCode,
                email: customer.email,
                firstname: customer.firstName,
                lastname: customer.lastName,
                telephone: customer.phone,
                regionCode: address.state
            };

            billingAddress = createBillingAddress(billingAddress);
            quote.billingAddress(billingAddress);
        },

        /**
         * Prepare data to place order
         * @param {Object} payload
         */
        beforePlaceOrder: function (payload) {
            this.setPaymentPayload(payload);

            if (this.isRequiredBillingAddress() || quote.billingAddress() === null)  {
                if (typeof payload.details.billingAddress !== 'undefined') {
                    this.setBillingAddress(payload.details, payload.details.billingAddress);
                } else {
                    this.setBillingAddress(payload.details, payload.details.shippingAddress);
                }
            }

            if (this.isSkipOrderReview()) {
                this.placeOrder();
            } else {
                this.customerEmail(payload.details.email);
                this.isReviewRequired(true);
            }
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            var self = this;

            $(self.paypalButtonSelector).html('');

            return BraintreePayPalCheckout.Button.render({
                env: this.getEnvironment(),
                style: {
                    color: 'blue',
                    shape: 'rect',
                    size: 'medium',
                    label: 'pay',
                    tagline: false
                },

                /**
                 * Creates a PayPal payment
                 */
                payment: function () {
                    return self.paypalCheckoutInstance.createPayment(
                        self.getPayPalConfig()
                    );
                },

                /**
                 * Tokenizes the authorize data
                 */
                onAuthorize: function (data) {
                    return self.paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {
                            self.beforePlaceOrder(payload);
                        });
                },

                /**
                 * Triggers on error
                 */
                onError: function () {
                    self.showError($t('Payment ' + self.getTitle() + ' can\'t be initialized'));
                    self.reInitPayPal();
                }
            }, self.paypalButtonSelector);
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
                config,
                isActiveVaultEnabler = this.isActiveVault();

            config = {
                flow: !isActiveVaultEnabler ? 'checkout' : 'vault',
                amount: this.grandTotalAmount,
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                enableShippingAddress: true,
                shippingAddressEditable: this.isAllowOverrideShippingAddress()
            };

            config.shippingAddressOverride = this.getShippingAddress();

            if (this.getMerchantName()) {
                config.displayName = this.getMerchantName();
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
                line1: _.isUndefined(address.street) || _.isUndefined(address.street[0]) ? '' : address.street[0],
                city: address.city,
                state: address.regionCode,
                postalCode: address.postcode,
                countryCode: address.countryId,
                phone: address.telephone,
                recipientName: address.firstname + ' ' + address.lastname
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
                    'payment_method_nonce': this.paymentPayload.nonce
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
         * @returns {String}
         */
        getEnvironment: function () {
            return window.checkoutConfig.payment[BraintreeAdapter.getCode()].environment;
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
            this.reInitPayPal();
        },

        /**
         * Show error message
         *
         * @param {String} errorMessage
         * @private
         */
        showError: function (errorMessage) {
            globalMessageList.addErrorMessage({
                message: errorMessage
            });
        }
    });
});
