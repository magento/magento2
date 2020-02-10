/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_CardinalCommerce/js/cardinal-client',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/model/messageList'
], function ($, cardinalClient, fullScreenLoader, globalMessageList) {
    'use strict';

    return function (originalComponent) {
        return originalComponent.extend({
            defaults: {
                cardinalJWT: null
            },

            /**
             * Performs 3d-secure authentication
             */
            beforePlaceOrder: function () {
                var original = this._super.bind(this),
                    client = cardinalClient,
                    isActive = window.checkoutConfig.cardinal.isActiveFor.authorizenet,
                    cardData;

                if (!isActive || !$(this.formElement).valid()) {
                    return original();
                }

                cardData = {
                    accountNumber: this.creditCardNumber(),
                    expMonth: this.creditCardExpMonth(),
                    expYear: this.creditCardExpYear()
                };

                if (this.hasVerification()) {
                    cardData.cardCode = this.creditCardVerificationNumber();
                }

                fullScreenLoader.startLoader();
                client.startAuthentication(cardData)
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    })
                    .done(function (jwt) {
                        this.cardinalJWT = jwt;
                        original();
                    }.bind(this))
                    .fail(function (errorMessage) {
                        globalMessageList.addErrorMessage({
                            message: errorMessage
                        });
                    });
            },

            /**
             * Adds cardinal response JWT to payment additional data.
             *
             * @returns {Object}
             */
            getData: function () {
                var originalData = this._super();

                originalData['additional_data'].cardinalJWT = this.cardinalJWT;

                return originalData;
            }
        });
    };
});
