/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function ($) {
    var when;

    return {
        when: function (url) {
            if (!when) {
                when = $.when($.get(url, {isAjax: true}));
            }
            return when;
        },

        request: function (url) {
            return $.get(url, {isAjax: true});
        }
    };
});
