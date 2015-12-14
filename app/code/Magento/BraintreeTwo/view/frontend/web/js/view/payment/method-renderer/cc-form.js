/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Magento_BraintreeTwo/js/view/payment/adapter',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Magento_BraintreeTwo/js/validator',
        'Magento_BraintreeTwo/js/view/payment/validator-handler'
    ],
    function (
        _,
        $,
        Component,
        quote,
        braintree,
        globalMessageList,
        $t,
        validator,
        validatorManager
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                isInitialized: false,
                braintreeClient: null,
                braintreeDeviceData: null,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                validatorManager: validatorManager,

                /**
                 * {String}
                 */
                connection: 'custom',

                /**
                 * Braintree client configuration
                 *
                 * {Object}
                 */
                clientConfig: {

                    /**
                     * {String}
                     */
                    id: 'co-transparent-form-braintree',

                    /**
                     * Device data initialization
                     *
                     * @param {Object} braintreeInstance
                     */
                    onReady: function (braintreeInstance) {
                        this.braintreeDeviceData = braintreeInstance.deviceData;
                    },

                    /**
                     * Triggers on payment nonce receive
                     *
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        this.paymentMethodNonce = response.nonce;
                        this.placeOrder();
                    },

                    /**
                     * Triggers on any Braintree error
                     */
                    onError: function () {
                        this.paymentMethodNonce = '';
                    }
                }
            },

            /**
             * Init config
             */
            initClientConfig: function () {
                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);

                // Advanced fraud tools settings
                this.clientConfig.dataCollector = this.configureDeviceDataCollector();
            },

            /**
             * @returns {Object}
             */
            configureDeviceDataCollector: function () {
                var fields = {
                    kount: {
                        environment: this.getEnvironment()
                    }
                };

                if (this.getKountMerchantId()) {
                    fields.kount.merchantId = this.getKountMerchantId();
                }

                return fields;
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active', 'isInitialized']);
                this.validatorManager.initialize();
                this.braintreeClient = braintree;
                this.initBraintree();

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return 'braintreetwo';
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
             * Init Braintree handlers
             */
            initBraintree: function () {
                if (!this.braintreeClient.getClientToken()) {
                    this.showError($t('Sorry, but something went wrong.'));
                }

                if (!this.isInitialized()) {
                    this.isInitialized(true);
                    this.initClient();
                }
            },

            /**
             * Init Braintree client
             */
            initClient: function () {
                this.initClientConfig();
                this.braintreeClient.getSdkClient().setup(
                    this.braintreeClient.getClientToken(),
                    this.connection,
                    this.clientConfig
                );
            },

            /**
             * Show error message
             *
             * @param {String} errorMessage
             */
            showError: function (errorMessage) {
                globalMessageList.addErrorMessage({
                    message: errorMessage
                });
            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {

                    return validator.collectTypes(
                        availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * @returns {String}
             */
            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            /**
             * @returns {String}
             */
            getKountMerchantId: function () {
                return window.checkoutConfig.payment[this.getCode()].kountMerchantId;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce,
                        'device_data': this.braintreeDeviceData
                    }
                };
            },

            /**
             * Action to place order
             *
             * @param {String} key
             */
            placeOrder: function (key) {
                var self = this;

                if (key) {
                    return self._super();
                }
                // place order on success validation
                self.validatorManager.validate(self, function () {
                    return self.placeOrder('parent');
                });

                return false;
            }
        });
    }
);
