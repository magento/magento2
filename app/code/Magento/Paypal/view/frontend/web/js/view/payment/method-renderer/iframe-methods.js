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
                template: 'Magento_Paypal/payment/iframe-methods'
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,
            /**
             * Get action url for payment mathod iframe.
             * @returns {String}
             */
            getActionUrl: function () {
                return this.isInAction() ? window.checkoutConfig.payment.paypalIframe.actionUrl[this.getCode()] : '';
            },
            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                this.placeOrder(false);
                this.isInAction(true);
            }
        });
    }
);
