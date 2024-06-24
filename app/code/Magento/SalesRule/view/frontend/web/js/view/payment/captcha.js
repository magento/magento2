/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Captcha/js/view/checkout/defaultCaptcha',
    'Magento_Captcha/js/model/captchaList',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_Checkout/js/model/quote',
    'ko'
], function (defaultCaptcha, captchaList, setCouponCodeAction, cancelCouponAction, quote, ko) {
    'use strict';

    return defaultCaptcha.extend({
        /** @inheritdoc */
        initialize: function () {
            var self = this,
                currentCaptcha,
                totals = quote.getTotals(),
                couponCode = ko.observable(null),
                couponCodeValue,
                isApplied;

            if (totals()) {
                couponCode(totals()['coupon_code']);
            }

            // Captcha can only be required for adding a coupon so we need to know if one was added already.
            couponCodeValue = couponCode();
            isApplied = ko.observable(typeof couponCodeValue === 'string' && couponCodeValue.length > 0);

            this._super();
            //Getting coupon captcha model.
            currentCaptcha = captchaList.getCaptchaByFormId(this.formId);

            if (currentCaptcha != null) {
                if (!isApplied()) {
                    //Show captcha if we don't have a coupon applied.
                    currentCaptcha.setIsVisible(true);
                }
                this.setCurrentCaptcha(currentCaptcha);
                //Add captcha code to coupon-apply request.
                setCouponCodeAction.registerDataModifier(function (headers) {
                    if (self.isRequired()) {
                        headers['X-Captcha'] = self.captchaValue()();
                    }
                });
                //Refresh captcha after failed request.
                setCouponCodeAction.registerFailCallback(function () {
                    if (self.isRequired()) {
                        self.refresh();
                    }
                });
                //Hide captcha when a coupon has been applied.
                setCouponCodeAction.registerSuccessCallback(function () {
                    self.setIsVisible(false);
                });
                //Show captcha again if it was canceled.
                cancelCouponAction.registerSuccessCallback(function () {
                    if (self.isRequired()) {
                        self.setIsVisible(true);
                    }
                });
            }
        }
    });
});
