/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/modal/alert'
], function (
    $,
    Component,
    messageList,
    $t,
    fullScreenLoader,
    setPaymentInformationAction,
    additionalValidators,
    alert
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Payment/payment/iframe',
            timeoutId: null,
            timeoutMessage: 'Sorry, but something went wrong.'
        },

        /**
         * @returns {String}
         */
        getSource: function () {
            return window.checkoutConfig.payment.iframe.source[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getControllerName: function () {
            return window.checkoutConfig.payment.iframe.controllerName[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getPlaceOrderUrl: function () {
            return window.checkoutConfig.payment.iframe.placeOrderUrl[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getCgiUrl: function () {
            return window.checkoutConfig.payment.iframe.cgiUrl[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getSaveOrderUrl: function () {
            return window.checkoutConfig.payment.iframe.saveOrderUrl[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getDateDelim: function () {
            return window.checkoutConfig.payment.iframe.dateDelim[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getCardFieldsMap: function () {
            return window.checkoutConfig.payment.iframe.cardFieldsMap[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getExpireYearLength: function () {
            return window.checkoutConfig.payment.iframe.expireYearLength[this.getCode()];
        },

        /**
         * @param {Object} parent
         * @returns {Function}
         */
        originalPlaceOrder: function (parent) {
            return parent.placeOrder.bind(parent);
        },

        /**
         * @returns {Number}
         */
        getTimeoutTime: function () {
            return window.checkoutConfig.payment.iframe.timeoutTime[this.getCode()];
        },

        /**
         * @returns {String}
         */
        getTimeoutMessage: function () {
            return $t(this.timeoutMessage);
        },

        /**
         * @override
         */
        placeOrder: function () {
            if (this.validateHandler() && additionalValidators.validate()) {

                fullScreenLoader.startLoader();

                this.isPlaceOrderActionAllowed(false);

                $.when(
                    setPaymentInformationAction(
                        this.messageContainer,
                        {
                            method: this.getCode()
                        }
                    )
                ).done(this.done.bind(this))
                    .fail(this.fail.bind(this));

                this.initTimeoutHandler();
            }
        },

        /**
         * {Function}
         */
        initTimeoutHandler: function () {
            this.timeoutId = setTimeout(
                this.timeoutHandler.bind(this),
                this.getTimeoutTime()
            );

            $(window).off('clearTimeout')
                .on('clearTimeout', this.clearTimeout.bind(this));
        },

        /**
         * {Function}
         */
        clearTimeout: function () {
            clearTimeout(this.timeoutId);
            this.fail();

            return this;
        },

        /**
         * {Function}
         */
        timeoutHandler: function () {
            this.clearTimeout();

            alert(
                {
                    content: this.getTimeoutMessage(),
                    actions: {

                        /**
                         * {Function}
                         */
                        always: this.alertActionHandler.bind(this)
                    }
                }
            );

            this.fail();
        },

        /**
         * {Function}
         */
        alertActionHandler: function () {
            fullScreenLoader.startLoader();
            window.location.reload();
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();
            this.isPlaceOrderActionAllowed(true);

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            this.placeOrderHandler().fail(function () {
                fullScreenLoader.stopLoader();
            });

            return this;
        }
    });
});
