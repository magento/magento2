/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Vault/js/view/payment/vault-enabler'
    ],
    function ($, Component, additionalValidators, setPaymentInformationAction, fullScreenLoader, vaultEnabler) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/payment/payflowpro-form'
            },
            placeOrderHandler: null,
            validateHandler: null,

            /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                this._super();
                this.vaultEnabler = vaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getCode());

                return this;
            },

            setPlaceOrderHandler: function(handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function(handler) {
                this.validateHandler = handler;
            },

            context: function() {
                return this;
            },

            isShowLegend: function() {
                return true;
            },

            getCode: function() {
                return 'payflowpro';
            },

            isActive: function() {
                return true;
            },

            /**
             * @override
             */
            placeOrder: function () {
                var self = this;

                if (this.validateHandler() && additionalValidators.validate()) {
                    fullScreenLoader.startLoader();
                    this.isPlaceOrderActionAllowed(false);
                    $.when(setPaymentInformationAction(this.messageContainer, self.getData())).done(function () {
                        self.placeOrderHandler().fail(function () {
                            fullScreenLoader.stopLoader();
                        });
                    }).fail(function () {
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);
                    });
                }
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_last_4': this.creditCardNumber().substr(-4)
                    }
                };

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },

            /**
             * @returns {Bool}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            }
        });
    }
);
