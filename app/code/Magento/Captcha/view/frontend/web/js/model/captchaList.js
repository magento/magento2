/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define(['jquery'], function ($) {
    'use strict';

    var captchaList = [];

    return {
        /**
         * @param {Object} captcha
         */
        add: function (captcha) {
            captchaList.push(captcha);
        },

        /**
         * @param {String} formId
         * @return {Object}
         */
        getCaptchaByFormId: function (formId) {
            var captcha = null;

            $.each(captchaList, function (key, item) {
                if (formId === item.formId) {
                    captcha = item;

                    return false;
                }
            });

            return captcha;
        },

        /**
         * @return {Array}
         */
        getCaptchaList: function () {
            return captchaList;
        }
    };
});
