/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (params, inputSelector) {
        var overlay = $(params.overlaySelector);

        $(inputSelector).on('change', function (event) {
            if ($(event.target).prop('checked')) {
                overlay.show();
            } else {
                overlay.hide();
            }
        });

        $(inputSelector).triggerHandler('change');
    };
});

