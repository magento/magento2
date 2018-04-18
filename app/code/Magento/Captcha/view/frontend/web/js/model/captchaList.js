/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(['jquery'], function($) {
    "use strict";
    var captchaList = [];
    return {
        add: function (captcha) {
            captchaList.push(captcha);
        },
        getCaptchaByFormId: function(formId) {
            var captcha = null;
            $.each(captchaList, function(key, item) {
                if (formId === item.formId) {
                    captcha = item;
                    return false;
                }
            });
            return captcha;
        },
        getCaptchaList: function() {
            return captchaList;
        }
    };
});
