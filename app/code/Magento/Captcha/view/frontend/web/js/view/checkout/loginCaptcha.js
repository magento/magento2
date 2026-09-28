/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Captcha/js/view/checkout/defaultCaptcha',
    'Magento_Captcha/js/model/captchaList',
    'Magento_Customer/js/action/login',
    'underscore'
],
function (defaultCaptcha, captchaList, loginAction, _) {
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

                loginAction.registerLoginCallback(function (loginData) {
                    if (loginData['captcha_form_id'] &&
                        loginData['captcha_form_id'] === self.formId &&
                        self.isRequired()
                    ) {
                        _.defer(self.refresh.bind(self));
                    }
                });
            }
        }
    });
});
