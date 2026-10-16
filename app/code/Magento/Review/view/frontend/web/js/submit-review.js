/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(window).on('pageshow', function (event) {
            if (event.originalEvent.persisted) {
                $(element).find('.submit').attr('disabled', false);
            }
        });

        $(element).on('submit', function () {
            if ($(this).valid()) {
                $(this).find('.submit').attr('disabled', true);
            }
        });
    };
});
