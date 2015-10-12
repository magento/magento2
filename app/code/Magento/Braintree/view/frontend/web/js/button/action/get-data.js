/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var when;

    return {

        /**
         * @param {String} url
         * @returns {*}
         */
        when: function (url) {
            if (!when) {
                when = $.when($.get(url, {
                    isAjax: true
                }));
            }

            return when;
        },

        /**
         * @param {String} url
         * @returns {*}
         */
        request: function (url) {
            return $.get(url, {
                isAjax: true
            });
        }
    };
});
