/**
 * Copyright © 2016 Magento. All rights reserved.
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
    function ($, Component, additionalValidators, setPaymentInformationAction, fullScreenLoader, VaultEnabler) {
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
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
            },

            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function(handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'payflowpro';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },

            /**
             * @override
             */
            placeOrder: function () {
                var self = this;

                if (this.validateHandler() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();
                    $.when(
                        setPaymentInformationAction(this.messageContainer, self.getData())
                    ).done(
                        function () {
                            self.placeOrderHandler().fail(
                                function () {
                                    fullScreenLoader.stopLoader();
                                }
                            );
                        }
                    ).always(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                        }
                    );
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
            },

            /**
             * @returns {String}
             */
            getVaultCode: function () {
                return 'payflowpro_cc_vault';
            }
        });
    }
);
