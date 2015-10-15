/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Magento_Paypal/js/model/iframe'
    ],
    function (Component, ko, iframe) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/payment/iframe-methods',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
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
                var self = this;
                this.afterPlaceOrder = function () {
                    self.paymentReady(true);
                };
                if (this.placeOrder()) {
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            }
        });
    }
);
