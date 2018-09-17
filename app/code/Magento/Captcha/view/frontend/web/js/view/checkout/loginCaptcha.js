/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'Magento_Captcha/js/view/checkout/defaultCaptcha',
        'Magento_Captcha/js/model/captchaList',
        'Magento_Customer/js/action/login'
    ],
    function (defaultCaptcha, captchaList, loginAction) {
        'use strict';
        return defaultCaptcha.extend({
            initialize: function() {
                this._super();
                var currentCaptcha = captchaList.getCaptchaByFormId(this.formId),
                    self = this;

                if (currentCaptcha != null) {
                    currentCaptcha.setIsVisible(true);
                    this.setCurrentCaptcha(currentCaptcha);

                    loginAction.registerLoginCallback(function(loginData) {
                        if (loginData.captcha_form_id && loginData.captcha_form_id == self.formId) {
                            self.refresh();
                        }
                    });
                }
            }
        });
    }
);
