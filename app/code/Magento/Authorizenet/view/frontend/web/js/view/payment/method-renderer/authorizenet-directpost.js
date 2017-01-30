/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, setPaymentInformationAction, additionalValidators, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Authorizenet/payment/authorizenet-directpost'
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
                return 'authorizenet_directpost';
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
                    $.when(setPaymentInformationAction(this.messageContainer, {
                        'method': self.getCode()
                    })).done(function () {
                        self.placeOrderHandler().fail(function () {
                            fullScreenLoader.stopLoader();
                        });
                    }).fail(function () {
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);
                    });
                }
            }
        });
    }
);
