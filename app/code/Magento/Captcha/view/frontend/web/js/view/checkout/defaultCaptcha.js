/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Captcha/js/model/captcha',
    'Magento_Captcha/js/model/captchaList'
], function ($, Component, Captcha, captchaList) {
    'use strict';

    var captchaConfig;

    return Component.extend({
        defaults: {
            template: 'Magento_Captcha/checkout/captcha'
        },
        dataScope: 'global',
        currentCaptcha: null,
        captchaValue: function () {//jscs:ignore jsDoc
            return this.currentCaptcha.getCaptchaValue();
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (window[this.configSource] && window[this.configSource].captcha) {
                captchaConfig = window[this.configSource].captcha;
                $.each(captchaConfig, function (formId, captchaData) {
                    captchaData.formId = formId;
                    captchaList.add(Captcha(captchaData));
                });
            }
        },

        /**
         * @return {Boolean}
         */
        getIsLoading: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.isLoading : false;
        },

        /**
         * @return {null|Object}
         */
        getCurrentCaptcha: function () {
            return this.currentCaptcha;
        },

        /**
         * @param {Object} captcha
         */
        setCurrentCaptcha: function (captcha) {
            this.currentCaptcha = captcha;
        },

        /**
         * @return {String|null}
         */
        getFormId: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getFormId() : null;
        },

        /**
         * @return {Boolean}
         */
        getIsVisible: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getIsVisible() : false;
        },

        /**
         * @param {Boolean} flag
         */
        setIsVisible: function (flag) {
            this.currentCaptcha.setIsVisible(flag);
        },

        /**
         * @return {Boolean}
         */
        isRequired: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getIsRequired() : false;
        },

        /**
         * @return {Boolean}
         */
        isCaseSensitive: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getIsCaseSensitive() : false;
        },

        /**
         * @return {String|Number|null}
         */
        imageHeight: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getImageHeight() : null;
        },

        /**
         * @return {String|null}
         */
        getImageSource: function () {
            return this.currentCaptcha !== null ? this.currentCaptcha.getImageSource() : null;
        },

        /**
         * Refresh captcha.
         */
        refresh: function () {
            this.currentCaptcha.refresh();
        }
    });
});
