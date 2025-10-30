/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
