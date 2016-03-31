/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, braintree, quote, $t) {
    'use strict';

    return {
        config: null,

        /**
         * Set 3d secure config
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
            this.config.thresholdAmount = parseFloat(config.thresholdAmount);
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function () {
            return 'three_d_secure';
        },

        /**
         * Validate Braintree payment nonce
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var client = braintree.getApiClient(),
                state = $.Deferred(),
                totalAmount = quote.totals()['base_grand_total'],
                billingAddress = quote.billingAddress();

            if (!this.isAmountAvailable(totalAmount) || !this.isCountryAvailable(billingAddress.countryId)) {
                state.resolve();

                return state.promise();
            }

            client.verify3DS({
                amount: totalAmount,
                creditCard: context.paymentMethodNonce
            }, function (error, response) {
                var liability;

                if (error) {
                    state.reject(error.message);

                    return;
                }

                liability = {
                    shifted: response.verificationDetails.liabilityShifted,
                    shiftPossible: response.verificationDetails.liabilityShiftPossible
                };

                if (liability.shifted || !liability.shifted && !liability.shiftPossible) {
                    context.paymentMethodNonce = response.nonce;
                    state.resolve();
                } else {
                    state.reject($t('Please try again with another form of payment.'));
                }
            });

            return state.promise();
        },

        /**
         * Check minimal amount for 3d secure activation
         * @param {Number} amount
         * @returns {Boolean}
         */
        isAmountAvailable: function (amount) {
            amount = parseFloat(amount);

            return amount >= this.config.thresholdAmount;
        },

        /**
         * Check if current country is available for 3d secure
         * @param {String} countryId
         * @returns {Boolean}
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
