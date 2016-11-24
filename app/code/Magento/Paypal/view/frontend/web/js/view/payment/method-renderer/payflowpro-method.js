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
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, additionalValidators, setPaymentInformationAction, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/payment/payflowpro-form'
            },
            placeOrderHandler: null,
            validateHandler: null,

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
                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();
                    $.when(
                        setPaymentInformationAction(this.messageContainer, {'method': self.getCode()})
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
            }
        });
    }
);
