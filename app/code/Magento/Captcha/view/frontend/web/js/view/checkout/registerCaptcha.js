/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'Magento_Captcha/js/view/checkout/defaultCaptcha',
        'Magento_Checkout/js/model/quote',
        'Magento_Captcha/js/model/captchaList'
    ],
    function (defaultCaptcha, quote, captchaList) {
        "use strict";
        return defaultCaptcha.extend({
            initialize: function() {
                this._super();
                var self = this;
                var currentCaptcha = captchaList.getCaptchaByFormId(this.formId);
                if (currentCaptcha != null) {
                    this.setCurrentCaptcha(currentCaptcha);
                    quote.getCheckoutMethod().subscribe(function(method) {
                        self.setIsVisible((method == 'register'));
                    });
                }
            }
        });
    }
);
