/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_BraintreeTwo/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, braintree, quote, $t) {
    'use strict';

    return {

        /**
         * Validate Braintree payment nonce
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var client = braintree.getApiClient(),
                state = $.Deferred();

            client.verify3DS({
                amount: quote.totals()['base_grand_total'],
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
        }
    };
});
