/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('submit', function () {
            if ($(this).valid()) {
                $(this).find('.submit').attr('disabled', true);
            }
        });
    };
});
