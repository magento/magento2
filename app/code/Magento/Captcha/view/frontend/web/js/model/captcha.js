/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global alert*/
define([
    'jquery',
    'ko',
    'Magento_Captcha/js/action/refresh'
], function ($, ko, refreshAction) {
    'use strict';

    return function (captchaData) {
        return {
            formId: captchaData.formId,
            imageSource: ko.observable(captchaData.imageSrc),
            visibility: ko.observable(false),
            captchaValue: ko.observable(null),
            isRequired: captchaData.isRequired,
            isCaseSensitive: captchaData.isCaseSensitive,
            imageHeight: captchaData.imageHeight,
            refreshUrl: captchaData.refreshUrl,
            isLoading: ko.observable(false),

            /**
             * @return {String}
             */
            getFormId: function () {
                return this.formId;
            },

            /**
             * @param {String} formId
             */
            setFormId: function (formId) {
                this.formId = formId;
            },

            /**
             * @return {Boolean}
             */
            getIsVisible: function () {
                return this.visibility;
            },

            /**
             * @param {Boolean} flag
             */
            setIsVisible: function (flag) {
                this.visibility(flag);
            },

            /**
             * @return {Boolean}
             */
            getIsRequired: function () {
                return this.isRequired;
            },

            /**
             * @param {Boolean} flag
             */
            setIsRequired: function (flag) {
                this.isRequired = flag;
            },

            /**
             * @return {Boolean}
             */
            getIsCaseSensitive: function () {
                return this.isCaseSensitive;
            },

            /**
             * @param {Boolean} flag
             */
            setIsCaseSensitive: function (flag) {
                this.isCaseSensitive = flag;
            },

            /**
             * @return {String|Number}
             */
            getImageHeight: function () {
                return this.imageHeight;
            },

            /**
             * @param {String|Number}height
             */
            setImageHeight: function (height) {
                this.imageHeight = height;
            },

            /**
             * @return {String}
             */
            getImageSource: function () {
                return this.imageSource;
            },

            /**
             * @param {String} imageSource
             */
            setImageSource: function (imageSource) {
                this.imageSource(imageSource);
            },

            /**
             * @return {String}
             */
            getRefreshUrl: function () {
                return this.refreshUrl;
            },

            /**
             * @param {String} url
             */
            setRefreshUrl: function (url) {
                this.refreshUrl = url;
            },

            /**
             * @return {*}
             */
            getCaptchaValue: function () {
                return this.captchaValue;
            },

            /**
             * @param {*} value
             */
            setCaptchaValue: function (value) {
                this.captchaValue(value);
            },

            /**
             * Refresh captcha.
             */
            refresh: function () {
                var refresh,
                    self = this;

                this.isLoading(true);

                refresh = refreshAction(this.getRefreshUrl(), this.getFormId(), this.getImageSource());
                $.when(refresh).done(function () {
                    self.isLoading(false);
                });
            }
        };
    };
});
