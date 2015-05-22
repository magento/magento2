/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'Magento_Captcha/js/view/checkout/defaultCaptcha',
        'Magento_Customer/js/model/customer',
        'Magento_Captcha/js/model/captchaList'
    ],
    function (defaultCaptcha, customer, captchaList) {
        "use strict";
        return defaultCaptcha.extend({
            initialize: function() {
                this._super();
                var currentCaptcha = captchaList.getCaptchaByFormId(this.formId);
                if (currentCaptcha != null) {
                    currentCaptcha.setIsVisible(true);
                    this.setCurrentCaptcha(currentCaptcha);
                    this.updateCaptchaOnFailedLogin();
                }
            },
            updateCaptchaOnFailedLogin: function () {
                if (this.formId == 'user_login') {
                    var self = this;
                    customer.getFailedLoginAttempts().subscribe(function() {
                        self.refresh();
                    });
                }
            }
        });
    }
);
