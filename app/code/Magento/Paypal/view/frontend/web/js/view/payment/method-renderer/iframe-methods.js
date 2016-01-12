/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Magento_Paypal/js/model/iframe',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, ko, iframe, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/payment/iframe-methods',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,

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
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            },

            getPlaceOrderDeferredObject: function () {
                var self = this;
                return this._super()
                    .fail(
                        function () {
                            self.isInAction(false);
                            document.removeEventListener('click', iframe.stopEventPropagation, true);
                        }
                    );
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                this.paymentReady(true);
            },

            /**
             * Hide loader when iframe is fully loaded.
             */
            iframeLoaded: function () {
                fullScreenLoader.stopLoader();
            }
        });
    }
);
