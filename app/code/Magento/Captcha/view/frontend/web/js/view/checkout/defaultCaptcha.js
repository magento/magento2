/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Captcha/js/model/captcha',
        'Magento_Captcha/js/model/captchaList'
    ],
    function ($, Component, Captcha, captchaList) {
        'use strict';
        var captchaConfig;

        return Component.extend({
            defaults: {
                template: 'Magento_Captcha/checkout/captcha'
            },
            dataScope: 'global',
            currentCaptcha: null,
            captchaValue: function() {
                return this.currentCaptcha.getCaptchaValue();
            },
            initialize: function() {
                this._super();
                captchaConfig = window[this.configSource]['captcha'];

                $.each(captchaConfig, function(formId, captchaData) {
                    captchaData.formId = formId;
                    captchaList.add(Captcha(captchaData));
                });
            },
            getIsLoading: function() {
                return this.currentCaptcha.isLoading
            },
            getCurrentCaptcha: function() {
                return this.currentCaptcha;
            },
            setCurrentCaptcha: function(captcha) {
                this.currentCaptcha = captcha;
            },
            getFormId: function() {
                return this.currentCaptcha.getFormId();
            },
            getIsVisible: function() {
                return this.currentCaptcha.getIsVisible();
            },
            setIsVisible: function(flag) {
                this.currentCaptcha.setIsVisible(flag);
            },
            isRequired: function() {
                return this.currentCaptcha.getIsRequired();
            },
            isCaseSensitive: function() {
                return this.currentCaptcha.getIsCaseSensitive();
            },
            imageHeight: function() {
                return this.currentCaptcha.getImageHeight();
            },
            getImageSource: function () {
                return this.currentCaptcha.getImageSource();
            },
            refresh: function() {
                this.currentCaptcha.refresh();
            }
        });
    }
);
