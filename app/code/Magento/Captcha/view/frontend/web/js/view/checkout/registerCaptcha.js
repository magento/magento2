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
        'Magento_Captcha/js/model/captchaList',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (defaultCaptcha, quote, captchaList, selectShippingAddress, selectBillingAddress, navigator) {
        "use strict";
        return defaultCaptcha.extend({
            initialize: function() {
                this._super();
                var self = this;
                var currentCaptcha = captchaList.getCaptchaByFormId(this.formId);
                if (currentCaptcha != null) {
                    this.setCurrentCaptcha(currentCaptcha);
                    quote.getCheckoutMethod().subscribe(function(method) {
                        if (method == 'register') {
                            self.setIsVisible(true);
                            var callback = function(isSuccessful) {
                                if (!isSuccessful) {
                                    currentCaptcha.setCaptchaValue(null);
                                    currentCaptcha.refresh();
                                    navigator.goToStep('billingAddress');
                                }
                            };
                            selectShippingAddress.setActionCallback(callback);
                            selectBillingAddress.setActionCallback(callback);
                        } else {
                            self.setIsVisible(false);
                        }
                    });
                }
            }
        });
    }
);
