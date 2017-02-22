/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
        return Component.extend({
            defaults: {
                template: 'Magento_Payment/payment/iframe'
            },
            getSource: function () {
                return window.checkoutConfig.payment.iframe.source[this.getCode()];
            },
            getControllerName: function() {
                return window.checkoutConfig.payment.iframe.controllerName[this.getCode()];
            },
            getPlaceOrderUrl: function() {
                return window.checkoutConfig.payment.iframe.placeOrderUrl[this.getCode()];
            },
            getCgiUrl: function() {
                return window.checkoutConfig.payment.iframe.cgiUrl[this.getCode()];
            },
            getSaveOrderUrl: function() {
                return window.checkoutConfig.payment.iframe.saveOrderUrl[this.getCode()];
            },
            getDateDelim: function() {
                return window.checkoutConfig.payment.iframe.dateDelim[this.getCode()];
            },
            getCardFieldsMap: function() {
                return window.checkoutConfig.payment.iframe.cardFieldsMap[this.getCode()];
            },
            originalPlaceOrder: function(parent) {
                return parent.placeOrder.bind(parent);
            },
            getExpireYearLength: function(parent) {
                return window.checkoutConfig.payment.iframe.expireYearLength[this.getCode()];
            }
        });
    }
);
