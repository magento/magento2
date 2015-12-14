/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
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
                validatorManager: validatorManager
            },

            /**
             * Set list of observable attributes
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
             * @returns {String}
             */
            getCode: function () {
                return 'braintreetwo';
            },

            /**
             * Check if payment is active
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
                    this.showError($t('Sorry, but something went wrong'));
                }

                if (!this.isInitialized()) {
                    this.isInitialized(true);
                    this.initBraintree();
                }
            },

            /**
             * Show error message
             * @param {String} errorMessage
             */
            showError: function (errorMessage) {
                globalMessageList.addErrorMessage({
                    message: errorMessage
                });
            },

            /**
             * Get full selector name
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
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

            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            getKountMerchantId: function () {
                return window.checkoutConfig.payment[this.getCode()].kountMerchantId;
            },

            /**
             * Get data
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
