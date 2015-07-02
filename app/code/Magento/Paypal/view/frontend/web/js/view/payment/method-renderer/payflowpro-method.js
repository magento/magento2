/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/iframe'
    ],
    function (Component) {
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

            placeOrder: function() {
                if (this.validateHandler()) {
                    this.placeOrderHandler();
                }
            }
        });
    }
);
