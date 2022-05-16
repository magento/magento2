/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'js-cookie/js.cookie'
], function ($, cookie) {
    'use strict';

    window.Cookies = window.Cookies || cookie;

    var config = $.cookie = function (key, value, options) {
        if (value !== undefined) {
            options = $.extend({}, config.defaults, options);

            return cookie.set(key, value, options);
        }

        var result = key ? undefined : {},
            cookies = document.cookie ? document.cookie.split('; ') : [],
            i;

        for (i = 0; i < cookies.length; i++) {
            var parts = cookies[i].split('='),
                name = config.raw ? parts.shift() : decodeURIComponent(parts.shift()),
                cookieValue = parts.join('=');

            if (key && key === name) {
                result = decodeURIComponent(cookieValue.replace('/\\+/g', ' '));
                break;
            }

            if (!key && (cookieValue = decodeURIComponent(cookieValue.replace('/\\+/g', ' '))) !== undefined) {
                result[name] = cookieValue;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
        if ($.cookie(key) === undefined) {
            return false;
        }

        $.cookie(key, '', $.extend({}, options, { expires: -1 }));
        return !$.cookie(key);
    };
});
