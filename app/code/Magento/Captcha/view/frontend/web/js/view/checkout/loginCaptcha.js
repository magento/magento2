/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'underscore',
        'Magento_Captcha/js/view/checkout/defaultCaptcha',
        'Magento_Captcha/js/model/captchaList',
        'Magento_Customer/js/action/login'
    ],
    function (_, defaultCaptcha, captchaList, loginAction) {
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

                    loginAction.registerLoginCallback(function (loginData, response) {
                        if (!loginData['captcha_form_id'] || loginData['captcha_form_id'] !== self.formId) {
                            return;
                        }

                        if (_.isUndefined(response) || !response.errors) {
                            return;
                        }

                        // check if captcha should be required after login attempt
                        if (!self.isRequired() && response.captcha && self.isRequired() !== response.captcha) {
                            self.setIsRequired(response.captcha);
                        }

                        self.refresh();
                    });
                }
            }
        });
    });
