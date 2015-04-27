/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/billing-address',
        'Magento_Captcha/js/model/captchaList',
        'Magento_Captcha/js/action/validate',
        'Magento_Checkout/js/model/quote',
        'mage/validation'
    ],
    function ($, billingAddressComponent, captchaList, validateAction, quote) {
        "use strict";
        return billingAddressComponent.extend({
            submitBillingAddress: function() {
                var formId = null,
                    orig = this.constructor.__super__.submitBillingAddress,
                    args = arguments,
                    self = this,
                    captchaCheckComplete = $.Deferred();

                if (quote.getCheckoutMethod()() == 'register') {
                    formId = 'register_during_checkout';
                } else if (quote.getCheckoutMethod()() == 'guest') {
                    formId = 'guest_checkout';
                }
                var currentCaptcha = captchaList.getCaptchaByFormId(formId);
                if (currentCaptcha === null || !currentCaptcha.getIsRequired()) {
                    orig.apply(self, args);
                    return;
                }

                this.validate();
                $("#co-billing-form").validation();

                if ($('#co-billing-form :input[name="captcha"]').valid() && !this.source.get('params.invalid')) {
                    validateAction(currentCaptcha, captchaCheckComplete);
                    $.when(captchaCheckComplete).done(function() {
                        orig.apply(self, args);
                    });
                }
            }
        });
    }
);
