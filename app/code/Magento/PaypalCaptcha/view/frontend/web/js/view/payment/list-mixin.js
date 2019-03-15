/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    var mixin = {
        /**
         * Sets custom template for Payflow Pro
         *
         * @param {Object} payment
         * @returns {Object}
         */
        createComponent: function (payment) {

            var component = this._super(payment);

            if (payment.method === 'payflowpro') {
                component.template = 'Magento_PaypalCaptcha/payment/payflowpro-form';
            }

            return component;
        }
    };

    /**
     * Overrides `Magento_Checkout/js/view/payment/list::createComponent`
     */
    return function (target) {
        return target.extend(mixin);
    };
});
