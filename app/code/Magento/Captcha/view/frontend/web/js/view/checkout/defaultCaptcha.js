/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Customer/js/model/customer',
        'Magento_Captcha/js/action/refresh'
    ],
    function ($, ko, Component, customer, refreshAction) {
        "use strict";
        var captchaConfig = window.checkoutConfig.captcha;
        return Component.extend({
            defaults: {
                template: 'Magento_Captcha/checkout/captcha'
            },
            imageSource: null,
            isRequired: function() {
                return captchaConfig[this.formId].isRequired;
            },
            isCaseSensitive: function() {
                return captchaConfig[this.formId].isCaseSensitive;
            },
            imageHeight: function() {
                return captchaConfig[this.formId].imageHeight;
            },
            getImageSource: function () {
                if (this.imageSource == null) {
                    this.imageSource = ko.observable(captchaConfig[this.formId].imageSrc);
                }
                return this.imageSource;
            },
            refresh: function() {
                refreshAction(captchaConfig[this.formId].refreshUrl, this.formId, this.imageSource);
            }
        });
    }
);
