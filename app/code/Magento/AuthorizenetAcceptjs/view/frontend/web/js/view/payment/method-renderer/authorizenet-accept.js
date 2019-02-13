/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_AuthorizenetAcceptjs/js/view/payment/acceptjs-client',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/model/messageList',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function ($, Component, AcceptjsClient, fullScreenLoader, globalMessageList) {
    'use strict';

    return Component.extend({
        defaults: {
            active: false,
            template: 'Magento_AuthorizenetAcceptjs/payment/authorizenet-acceptjs',
            tokens: null,
            ccForm: 'Magento_Payment/payment/cc-form',
            acceptjsClient: null
        },

        /**
         * Set list of observable attributes
         *
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe(['active']);

            return this;
        },

        /**
         * @returns {String}
         */
        getCode: function () {
            return 'authorizenet_acceptjs';
        },

        /**
         * Initialize form elements for validation
         */
        initFormElement: function (element) {
            this.formElement = element;
            this.acceptjsClient = AcceptjsClient({
                environment: window.checkoutConfig.payment[this.getCode()].environment
            });
            $(this.formElement).validation();
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            return {
                method: this.getCode(),
                'additional_data': {
                    opaqueDataDescriptor: this.tokens ? this.tokens.opaqueDataDescriptor : null,
                    opaqueDataValue: this.tokens ? this.tokens.opaqueDataValue : null,
                    ccLast4: this.creditCardNumber().substr(-4)
                }
            };
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
         * Prepare data to place order
         */
        beforePlaceOrder: function () {
            var authData = {},
                cardData = {},
                secureData = {};

            if (!$(this.formElement).valid()) {
                return;
            }

            authData.clientKey = window.checkoutConfig.payment[this.getCode()].clientKey;
            authData.apiLoginID = window.checkoutConfig.payment[this.getCode()].apiLoginID;

            cardData.cardNumber = this.creditCardNumber();
            cardData.month = this.creditCardExpMonth();
            cardData.year = this.creditCardExpYear();

            if (this.hasVerification()) {
                cardData.cardCode = this.creditCardVerificationNumber();
            }

            secureData.authData = authData;
            secureData.cardData = cardData;

            fullScreenLoader.startLoader();

            this.acceptjsClient.createTokens(secureData)
                .always(function () {
                    fullScreenLoader.stopLoader();
                })
                .done(function (tokens) {
                    this.tokens = tokens;
                    this.placeOrder();
                }.bind(this))
                .fail(function (messages) {
                    this.tokens = null;
                    this._showErrors(messages);
                }.bind(this));
        },

        /**
         * Should the cvv field be used
         *
         * @return {Boolean}
         */
        hasVerification: function () {
            return window.checkoutConfig.payment[this.getCode()].useCvv;
        },

        /**
         * Show error messages
         *
         * @param {String[]} errorMessages
         */
        _showErrors: function (errorMessages) {
            $.each(errorMessages, function (index, message) {
                globalMessageList.addErrorMessage({
                    message: message
                });
            });
        }
    });
});
