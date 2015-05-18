/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'Magento_Captcha/js/action/refresh'
    ],
    function(ko, refreshAction) {
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

                getFormId: function () {
                    return this.formId;
                },
                setFormId: function (formId) {
                    this.formId = formId;
                },
                getIsVisible: function () {
                    return this.visibility;
                },
                setIsVisible: function (flag) {
                    this.visibility(flag);
                },
                getIsRequired: function () {
                    return this.isRequired;
                },
                setIsRequired: function (flag) {
                    this.isRequired = flag;
                },
                getIsCaseSensitive: function () {
                    return this.isCaseSensitive;
                },
                setIsCaseSensitive: function (flag) {
                    this.isCaseSensitive = flag;
                },
                getImageHeight: function () {
                    return this.imageHeight;
                },
                setImageHeight: function (height) {
                    this.imageHeight = height;
                },
                getImageSource: function () {
                    return this.imageSource;
                },
                setImageSource: function (imageSource) {
                     this.imageSource(imageSource);
                },
                getRefreshUrl: function () {
                    return this.refreshUrl;
                },
                setRefreshUrl: function (url) {
                    this.refreshUrl = url;
                },
                getCaptchaValue: function () {
                    return this.captchaValue;
                },
                setCaptchaValue: function (value) {
                    this.captchaValue(value);
                },
                refresh: function() {
                    refreshAction(this.getRefreshUrl(), this.getFormId(), this.getImageSource());
                }
            };
        }
    }
);
