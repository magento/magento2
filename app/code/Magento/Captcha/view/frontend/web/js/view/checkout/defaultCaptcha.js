/**
 * Copyright © 2016 Magento. All rights reserved.
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

                if (window[this.configSource] && window[this.configSource]['captcha']) {
                    captchaConfig = window[this.configSource]['captcha'];
                    $.each(captchaConfig, function(formId, captchaData) {
                        captchaData.formId = formId;
                        captchaList.add(Captcha(captchaData));
                    });
                }
            },
            getIsLoading: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.isLoading : false;
            },
            getCurrentCaptcha: function() {
                return this.currentCaptcha;
            },
            setCurrentCaptcha: function(captcha) {
                this.currentCaptcha = captcha;
            },
            getFormId: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.getFormId() : null;
            },
            getIsVisible: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.getIsVisible() : false;
            },
            setIsVisible: function(flag) {
                this.currentCaptcha.setIsVisible(flag);
            },
            isRequired: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.getIsRequired() : false;
            },
            isCaseSensitive: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.getIsCaseSensitive() : false;
            },
            imageHeight: function() {
                return this.currentCaptcha !== null ? this.currentCaptcha.getImageHeight() : null;
            },
            getImageSource: function () {
                return this.currentCaptcha !== null ? this.currentCaptcha.getImageSource() : null;
            },
            refresh: function() {
                this.currentCaptcha.refresh();
            }
        });
    }
);
