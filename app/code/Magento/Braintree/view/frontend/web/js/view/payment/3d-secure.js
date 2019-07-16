/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'braintree3DSecure',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    braintree3DSecure,
    braintreeAdapter,
    quote,
    $t,
    Modal,
    fullScreenLoader
) {
    'use strict';

    return {
        config: null,
        modal: null,
        threeDSecureInstance: null,
        state: null,

        /**
         * Initializes component
         */
        initialize: function () {
            var self = this,
                promise = $.Deferred();

            self.state = $.Deferred();
            braintreeAdapter.getApiClient()
                .then(function (clientInstance) {
                    return braintree3DSecure.create({
                        client: clientInstance
                    });
                })
                .then(function (threeDSecureInstance) {
                    self.threeDSecureInstance = threeDSecureInstance;
                    promise.resolve(self.threeDSecureInstance);
                })
                .catch(function (err) {
                    promise.reject(err);
                });

            return promise.promise();
        },

        /**
         * Sets 3D Secure config
         *
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
            this.config.thresholdAmount = parseFloat(config.thresholdAmount);
        },

        /**
         * Gets code
         *
         * @returns {String}
         */
        getCode: function () {
            return 'three_d_secure';
        },

        /**
         * Validates 3D Secure
         *
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var self = this,
                totalAmount = quote.totals()['base_grand_total'],
                billingAddress = quote.billingAddress(),
                options = {
                    amount: totalAmount,
                    nonce: context.paymentPayload.nonce,

                    /**
                     * Adds iframe to page
                     * @param {Object} err
                     * @param {Object} iframe
                     */
                    addFrame: function (err, iframe) {
                        self.createModal($(iframe));
                        fullScreenLoader.stopLoader();
                        self.modal.openModal();
                    },

                    /**
                     * Removes iframe from page
                     */
                    removeFrame: function () {
                        self.modal.closeModal();
                    }
                };

            if (!this.isAmountAvailable(totalAmount) || !this.isCountryAvailable(billingAddress.countryId)) {
                self.state.resolve();

                return self.state.promise();
            }

            fullScreenLoader.startLoader();
            this.initialize()
                .then(function () {
                    self.threeDSecureInstance.verifyCard(options, function (err, payload) {
                        if (err) {
                            self.state.reject(err.message);

                            return;
                        }

                        // `liabilityShifted` indicates that 3DS worked and authentication succeeded
                        // if `liabilityShifted` and `liabilityShiftPossible` are false - card is ineligible for 3DS
                        if (payload.liabilityShifted || !payload.liabilityShifted && !payload.liabilityShiftPossible) {
                            context.paymentPayload.nonce = payload.nonce;
                            self.state.resolve();
                        } else {
                            self.state.reject($t('Please try again with another form of payment.'));
                        }
                    });
                })
                .fail(function () {
                    fullScreenLoader.stopLoader();
                    self.state.reject($t('Please try again with another form of payment.'));
                });

            return self.state.promise();
        },

        /**
         * Creates modal window
         *
         * @param {Object} $context
         * @private
         */
        createModal: function ($context) {
            var self = this,
                options = {
                    clickableOverlay: false,
                    buttons: [],
                    modalCloseBtnHandler: self.cancelFlow.bind(self),
                    keyEventHandlers: {
                        escapeKey: self.cancelFlow.bind(self)
                    }
                };

            // adjust iframe styles
            $context.attr('width', '100%');
            self.modal = Modal(options, $context);
        },

        /**
         * Cancels 3D Secure flow
         *
         * @private
         */
        cancelFlow: function () {
            var self = this;

            self.threeDSecureInstance.cancelVerifyCard(function () {
                self.modal.closeModal();
                self.state.reject();
            });
        },

        /**
         * Checks minimal amount for 3D Secure activation
         *
         * @param {Number} amount
         * @returns {Boolean}
         * @private
         */
        isAmountAvailable: function (amount) {
            amount = parseFloat(amount);

            return amount >= this.config.thresholdAmount;
        },

        /**
         * Checks if current country is available for 3D Secure
         *
         * @param {String} countryId
         * @returns {Boolean}
         * @private
         */
        isCountryAvailable: function (countryId) {
            var key,
                specificCountries = this.config.specificCountries;

            // all countries are available
            if (!specificCountries.length) {
                return true;
            }

            for (key in specificCountries) {
                if (countryId === specificCountries[key]) {
                    return true;
                }
            }

            return false;
        }
    };
});
