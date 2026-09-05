/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).on('save', function () {
            if ($(this).valid()) {
                $('body').trigger('processStart');
            }
        });
    };
});
