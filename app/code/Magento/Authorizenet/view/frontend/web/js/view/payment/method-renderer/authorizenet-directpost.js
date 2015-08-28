/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, Component, setPaymentInformationAction) {
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

            placeOrder: function() {
                var self = this;
                if (this.validateHandler()) {
                    this.isPlaceOrderActionAllowed(false);
                    $.when(setPaymentInformationAction()).done(function() {
                        self.placeOrderHandler();
                    }).fail(function() {
                        self.isPlaceOrderActionAllowed(true);
                    });
                }
            }
        });
    }
);
