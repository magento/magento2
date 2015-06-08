/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Captcha/js/view/checkout/defaultCaptcha',
        'Magento_Customer/js/model/customer',
        'Magento_Captcha/js/model/captchaList',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (defaultCaptcha, customer, captchaList, selectShippingAddress, selectBillingAddress, navigator) {
        'use strict';

        return defaultCaptcha.extend({
            /**
             * Initialize captcha for guest
             */
            initialize: function () {
                var self = this,
                    currentCaptcha = captchaList.getCaptchaByFormId(this.formId),
                    /**
                     * Callback for successful login
                     * @param {Boolean} isSuccessful
                     */
                    callback = function (isSuccessful) {
                        if (!isSuccessful) {
                            currentCaptcha.setCaptchaValue(null);
                            currentCaptcha.refresh();
                            navigator.goToStep('billingAddress');
                        }
                    };
                this._super();

                if (currentCaptcha != null) {
                    this.setCurrentCaptcha(currentCaptcha);
                    customer.isLoggedIn.subscribe(function (isLoggedIn) {
                        if (!isLoggedIn) {
                            self.setIsVisible(true);
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
