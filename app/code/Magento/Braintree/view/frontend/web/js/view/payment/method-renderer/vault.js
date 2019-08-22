/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, VaultComponent, Braintree, globalMessageList, fullScreenLoader) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Magento_Vault/payment/form',
            modules: {
                hostedFields: '${ $.parentName }.braintree'
            }
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * Place order
         */
        placeOrder: function () {
            var self = this;

            self.getPaymentMethodNonce();
        },

        /**
         * Send request to get payment method nonce
         */
        getPaymentMethodNonce: function () {
            var self = this;

            fullScreenLoader.startLoader();
            $.getJSON(self.nonceUrl, {
                'public_hash': self.publicHash
            })
                .done(function (response) {
                    fullScreenLoader.stopLoader();
                    self.hostedFields(function (formComponent) {
                        formComponent.paymentPayload.nonce = response.paymentMethodNonce;
                        formComponent.additionalData['public_hash'] = self.publicHash;
                        formComponent.code = self.code;
                        formComponent.messageContainer = self.messageContainer;
                        formComponent.placeOrder();
                    });
                })
                .fail(function (response) {
                    var error = JSON.parse(response.responseText);

                    fullScreenLoader.stopLoader();
                    globalMessageList.addErrorMessage({
                        message: error.message
                    });
                });
        }
    });
});
