/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Captcha/js/model/captcha',
    'Magento_Captcha/js/model/captchaList',
    'Magento_Customer/js/customer-data',
    'underscore'
], function ($, Component, Captcha, captchaList, customerData, _) {
    'use strict';

    var captchaConfig;

    return Component.extend({
        defaults: {
            template: 'Magento_Captcha/checkout/captcha'
        },
        dataScope: 'global',
        currentCaptcha: null,
        subscribedFormIds: [],

        /**
         * @return {*}
         */
        captchaValue: function () {
            return this.currentCaptcha.getCaptchaValue();
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (window[this.configSource] && window[this.configSource].captcha) {
                captchaConfig = window[this.configSource].captcha;
                $.each(captchaConfig, function (formId, captchaData) {
                    var captcha;

                    captchaData.formId = formId;
                    captcha = Captcha(captchaData);
                    this.checkCustomerData(formId, customerData.get('captcha')(), captcha);
                    this.subscribeCustomerData(formId, captcha);
                    captchaList.add(captcha);
                }.bind(this));
            }
        },

        /**
         * Check customer data for captcha configuration.
         *
         * @param {String} formId
         * @param {Object} captchaData
         * @param {Object} captcha
         */
        checkCustomerData: function (formId, captchaData, captcha) {
            if (!_.isEmpty(captchaData) &&
                !_.isEmpty(captchaData[formId]) &&
                captchaData[formId].timestamp > captcha.timestamp
            ) {
                if (!captcha.isRequired() && captchaData[formId].isRequired) {
                    captcha.refresh();
                }
                captcha.isRequired(captchaData[formId].isRequired);
                captcha.timestamp = captchaData[formId].timestamp;
            }
        },

        /**
         * Subscribe for customer data updates.
         *
         * @param {String} formId
         * @param {Object} captcha
         */
        subscribeCustomerData: function (formId, captcha) {
            if (this.subscribedFormIds.includes(formId) === false) {
                this.subscribedFormIds.push(formId);
                customerData.get('captcha').subscribe(function (captchaData) {
                    this.checkCustomerData(formId, captchaData, captcha);
                }.bind(this));
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
         * Set isRequired on current captcha model.
         *
         * @param {Boolean} flag
         */
        setIsRequired: function (flag) {
            this.currentCaptcha.setIsRequired(flag);
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
