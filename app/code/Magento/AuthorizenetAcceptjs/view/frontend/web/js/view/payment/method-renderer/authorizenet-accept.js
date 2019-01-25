/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'acceptjs',
    'Magento_AuthorizenetAcceptjs/js/view/payment/validator-handler',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function ($, Component, acceptjs, validatorHandler, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_AuthorizenetAcceptjs/payment/authorizenet-acceptjs',
            authnetResponse: null,
            ccForm: 'Magento_Payment/payment/cc-form'
        },

        /**
         * Set list of observable attributes
         *
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            validatorHandler.initialize();

            return this._super();
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
            $(this.formElement).validation();
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            return {
                method: this.getCode(),
                'additional_data': {
                    opaqueDataDescriptor: this.authnetResponse ? this.authnetResponse.opaqueData.dataDescriptor : null,
                    opaqueDataValue: this.authnetResponse ? this.authnetResponse.opaqueData.dataValue : null
                }
            };
        },

        /**
         * @returns {Boolean}
         */
        isActive: function () {
            return this.getCode() === this.isChecked();
        },

        /**
         * Prepare data to place order
         */
        beforePlaceOrder: function () {
            var authData = {},
                cardData = {},
                secureData = {};

            if ($(this.formElement).valid()) {
                authData.clientKey = window.checkoutConfig.payment[this.getCode()].clientKey;
                authData.apiLoginID = window.checkoutConfig.payment[this.getCode()].apiLoginID;

                cardData.cardNumber = this.creditCardNumber();
                cardData.month = this.creditCardExpMonth();
                cardData.year = this.creditCardExpYear();
                cardData.cardCode = this.creditCardVerificationNumber();

                secureData.authData = authData;
                secureData.cardData = cardData;

                acceptjs.dispatchData(secureData, this.handleResponse.bind(this));
            }
        },

        /**
         * Handle response from authnet-acceptjs
         */
        handleResponse: function (response) {
            this.authnetResponse = response;
            this.placeOrder();
        },

        /**
         * Action to place order
         */
        placeOrder: function () {
            var self = this;

            fullScreenLoader.startLoader();

            validatorHandler.validate(this.authnetResponse, function (valid) {
                fullScreenLoader.stopLoader();

                if (valid) {
                    return self._super();
                }
            });

            return false;
        }
    });
})
