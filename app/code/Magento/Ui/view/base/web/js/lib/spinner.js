/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var selector = '[data-role="spinner"]',
        spinner = $(selector);

    return {
        show: function () {
            spinner.show();
        },

        hide: function () {
            spinner.hide();
        },

        get: function (id) {
            return $(selector + '[data-component="' + id + '"]');
        }
    };
});
