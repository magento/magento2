/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'braintreeDataCollector',
    'Magento_Braintree/js/view/payment/adapter'
], function (
    $,
    braintreeDataCollector,
    braintreeAdapter
) {
    'use strict';

    return {
        paymentCode: 'braintree',

        /**
         * Returns information about a customer's device on checkout page for passing to Kount for review.
         *
         * @returns {Object}
         */
        getDeviceData: function () {
            var state = $.Deferred();

            if (this.hasFraudProtection()) {
                braintreeAdapter.getApiClient()
                    .then(function (clientInstance) {
                        return braintreeDataCollector.create({
                            client: clientInstance,
                            kount: true
                        });
                    })
                    .then(function (dataCollectorInstance) {
                        var deviceData = dataCollectorInstance.deviceData;

                        state.resolve(deviceData);
                    })
                    .catch(function (err) {
                        state.reject(err);
                    });
            }

            return state.promise();
        },

        /**
         * Returns setting value.
         *
         * @returns {Boolean}
         * @private
         */
        hasFraudProtection: function () {
            return window.checkoutConfig.payment[this.paymentCode].hasFraudProtection;
        }
    };
});
