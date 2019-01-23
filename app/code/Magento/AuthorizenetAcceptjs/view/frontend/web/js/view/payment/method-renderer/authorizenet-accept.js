/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'mage/translate',
        'acceptjs'
    ],
    function ($, Component, $t, acceptjs) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_AuthorizenetAcceptjs/payment/authorizenet-acceptjs',
                timeoutMessage: $t('Sorry, but something went wrong. Please contact the seller.')
            },
            dataDescriptor: null,
            dataValue: null,
            authnetError: false,

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'authorizenet_acceptjs';
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'data_descriptor': this.dataDescriptor,
                        'data_value': this.dataValue
                    }
                };
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },

            /**
             * Prepare data to place order
             */
            beforePlaceOrder: function () {
                var payment_type = this.getCode();

                var authData = {};
                //TODO: Add config provider
                 authData.clientKey = window.checkoutConfig.payment[this.getCode()].clientKey;
                 authData.apiLoginID = window.checkoutConfig.payment[this.getCode()].apiLoginID;

                var cardData = {};
                cardData.cardNumber = $("#" + payment_type + "_cc_number").val();
                cardData.month = $("#" + payment_type + "_expiration").val();
                cardData.year = $("#" + payment_type + "_expiration_yr").val();
                cardData.cardCode = $("#" + payment_type + "_cc_cid").val();

                var secureData = {};
                secureData.authData = authData;
                secureData.cardData = cardData;

                Accept.dispatchData(secureData, this.handleResponse.bind(this));
            },

            /**
             * Handle response from authnet-acceptjs
             */
            handleResponse: function (response) {
                if (response.messages.resultCode === 'Ok') {
                    this.dataDescriptor = response.opaqueData.dataDescriptor;
                    this.dataValue = response.opaqueData.dataValue;
                    //clear cc fields on success response
                    $("#" + this.getCode() + "_cc_number").val('');
                    $("#" + this.getCode() + "_expiration").val('');
                    $("#" + this.getCode() + "_expiration_yr").val('');
                    $("#" + this.getCode() + "_cc_cid").val();
                    this.placeOrder();
                } else {
                    //TODO: Add Validation Handling part
                    this.authnetError = true;
                }
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
                if (this.authnetError === false) {
                    return self.placeOrder('parent');
                }

                return false;
            }
        });
    });