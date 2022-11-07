/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Captcha/js/view/checkout/defaultCaptcha',
    'Magento_Captcha/js/model/captchaList',
    'underscore',
    'Magento_Checkout/js/model/payment/place-order-hooks'
],
function (defaultCaptcha, captchaList, _, placeOrderHooks) {
    'use strict';

    return defaultCaptcha.extend({
        /** @inheritdoc */
        initialize: function () {
            var self = this,
                currentCaptcha;

            this._super();
            currentCaptcha = captchaList.getCaptchaByFormId(this.formId);

            if (currentCaptcha != null) {
                currentCaptcha.setIsVisible(true);
                this.setCurrentCaptcha(currentCaptcha);
                placeOrderHooks.requestModifiers.push(function (headers) {
                    if (self.isRequired()) {
                        headers['X-Captcha'] = self.captchaValue()();
                    }
                });
                placeOrderHooks.afterRequestListeners.push(function () {
                    self.refresh();
                });
            }
        }
    });
});
