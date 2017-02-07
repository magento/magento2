/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    var selector = '[data-role="spinner"]',
        spinner = $(selector);

    return {
        /**
         * Show spinner.
         */
        show: function () {
            spinner.show();
        },

        /**
         * Hide spinner.
         */
        hide: function () {
            spinner.hide();
        },

        /**
         * Get spinner by selector.
         *
         * @param {String} id
         * @return {jQuery}
         */
        get: function (id) {
            return $(selector + '[data-component="' + id + '"]');
        }
    };
});
