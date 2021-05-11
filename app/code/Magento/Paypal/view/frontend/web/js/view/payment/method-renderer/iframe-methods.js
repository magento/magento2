/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Paypal/js/model/iframe',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Paypal/js/view/paylater-common'
], function (Component, iframe, fullScreenLoader, paypalPayLater) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/payment/iframe-methods',
            paymentReady: false
        },
        redirectAfterPlaceOrder: false,
        isInAction: iframe.isInAction,
        amount: 0,

        /** Init observable variables */
        initialize: function () {
            this._super();

            if (window.checkoutConfig) {
                const config = window.checkoutConfig.payment.paypalPayLater.config;
                paypalPayLater.init(config.sdkUrl, config.attributes);
            }

            return this;
        },

        /**
         * @return {exports}
         */
        initObservable: function () {
            this._super()
                .observe('paymentReady');

            return this;
        },

        /**
         * @return {*}
         */
        isPaymentReady: function () {
            return this.paymentReady();
        },

        /**
         * Get action url for payment method iframe.
         * @returns {String}
         */
        getActionUrl: function () {
            return this.isInAction() ? window.checkoutConfig.payment.paypalIframe.actionUrl[this.getCode()] : '';
        },

        /**
         * Places order in pending payment status.
         */
        placePendingPaymentOrder: function () {
            if (this.placeOrder()) {
                fullScreenLoader.startLoader();
                this.isInAction(true);
                // capture all click events
                document.addEventListener('click', iframe.stopEventPropagation, true);
            }
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            var self = this;

            return this._super().fail(function () {
                fullScreenLoader.stopLoader();
                self.isInAction(false);
                document.removeEventListener('click', iframe.stopEventPropagation, true);
            });
        },

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            if (this.iframeIsLoaded) {
                document.getElementById(this.getCode() + '-iframe')
                    .contentWindow.location.reload();
            }

            this.paymentReady(true);
            this.iframeIsLoaded = true;
            this.isPlaceOrderActionAllowed(true);
            fullScreenLoader.stopLoader();
        },

        /**
         * Hide loader when iframe is fully loaded.
         */
        iframeLoaded: function () {
            fullScreenLoader.stopLoader();
        },

        /** Returns payment paylater enabled */
        getPayLaterEnabled: function () {
            return window.checkoutConfig.payment.paypalPayLater.enabled;
        },

        /**
         * Get PayLater attribute value from configuration
         *
         * @param {String} attributeName
         * @returns {*|null}
         */
        getAttribute: function (attributeName) {
            return paypalPayLater.getAttribute(attributeName);
        }
    });
});
