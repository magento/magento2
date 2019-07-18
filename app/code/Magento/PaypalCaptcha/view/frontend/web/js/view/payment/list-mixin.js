/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Captcha/js/model/captchaList'
], function ($, captchaList) {
    'use strict';

    var mixin = {

        formId: 'co-payment-form',

        /**
         * Sets custom template for Payflow Pro
         *
         * @param {Object} payment
         * @returns {Object}
         */
        createComponent: function (payment) {

            var component = this._super(payment);

            if (component.component === 'Magento_Paypal/js/view/payment/method-renderer/payflowpro-method') {
                component.template = 'Magento_PaypalCaptcha/payment/payflowpro-form';
                $(window).off('clearTimeout')
                    .on('clearTimeout', this.clearTimeout.bind(this));
            }

            return component;
        },

        /**
         * Overrides default window.clearTimeout() to catch errors from iframe and reload Captcha.
         */
        clearTimeout: function () {
            var captcha = captchaList.getCaptchaByFormId(this.formId);

            if (captcha !== null) {
                captcha.refresh();
            }
            clearTimeout();
        }
    };

    /**
     * Overrides `Magento_Checkout/js/view/payment/list::createComponent`
     */
    return function (target) {
        return target.extend(mixin);
    };
});
