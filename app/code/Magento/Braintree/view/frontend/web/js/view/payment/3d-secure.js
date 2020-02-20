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
                        version: 2, // Will use 3DS 2 whenever possible
                        client: clientInstance
                    });
                })
                .then(function (threeDSecureInstance) {
                    self.threeDSecureInstance = threeDSecureInstance;
                    promise.resolve(self.threeDSecureInstance);
                })
                .catch(function (err) {
                    fullScreenLoader.stopLoader();
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
                shippingAddress = quote.shippingAddress(),
                options = {
                    amount: totalAmount,
                    nonce: context.paymentPayload.nonce,
                    billingAddress: {
                        givenName: billingAddress.firstname,
                        surname: billingAddress.lastname,
                        phoneNumber: billingAddress.telephone,
                        streetAddress: billingAddress.street[0],
                        extendedAddress: billingAddress.street[1],
                        locality: billingAddress.city,
                        region: billingAddress.regionCode,
                        postalCode: billingAddress.postcode,
                        countryCodeAlpha2: billingAddress.countryId
                    },

                    /**
                     * Will be called after receiving ThreeDSecure response, before completing the flow.
                     *
                     * @param {Object} data - ThreeDSecure data to consume before continuing
                     * @param {Function} next - callback to continue flow
                     */
                    onLookupComplete: function (data, next) {
                        next();
                    }
                };

            if (context.paymentPayload.details) {
                options.bin = context.paymentPayload.details.bin;
            }

            if (shippingAddress && this.isValidShippingAddress(shippingAddress)) {
                options.additionalInformation = {
                    shippingGivenName: shippingAddress.firstname,
                    shippingSurname: shippingAddress.lastname,
                    shippingPhone: shippingAddress.telephone,
                    shippingAddress: {
                        streetAddress: shippingAddress.street[0],
                        extendedAddress: shippingAddress.street[1],
                        locality: shippingAddress.city,
                        region: shippingAddress.regionCode,
                        postalCode: shippingAddress.postcode,
                        countryCodeAlpha2: shippingAddress.countryId
                    }
                };
            }

            if (!this.isAmountAvailable(totalAmount) || !this.isCountryAvailable(billingAddress.countryId)) {
                self.state = $.Deferred();
                self.state.resolve();

                return self.state.promise();
            }

            fullScreenLoader.startLoader();
            this.initialize()
                .then(function () {
                    self.threeDSecureInstance.verifyCard(options, function (err, payload) {
                        if (err) {
                            fullScreenLoader.stopLoader();
                            self.state.reject(err.message);

                            return;
                        }

                        // `liabilityShifted` indicates that 3DS worked and authentication succeeded
                        // if `liabilityShifted` and `liabilityShiftPossible` are false - card is ineligible for 3DS
                        if (payload.liabilityShifted || !payload.liabilityShifted && !payload.liabilityShiftPossible) {
                            context.paymentPayload.nonce = payload.nonce;
                            self.state.resolve();
                        } else {
                            fullScreenLoader.stopLoader();
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
        },

        /**
         * Validate shipping address
         *
         * @param {Object} shippingAddress
         * @return {Boolean}
         */
        isValidShippingAddress: function (shippingAddress) {
            var isValid = false;

            // check that required fields are not empty
            if (shippingAddress.firstname && shippingAddress.lastname && shippingAddress.telephone &&
                shippingAddress.street && shippingAddress.city && shippingAddress.regionCode &&
                shippingAddress.postcode && shippingAddress.countryId) {
                isValid = true;
            }

            return isValid;
        }
    };
});
