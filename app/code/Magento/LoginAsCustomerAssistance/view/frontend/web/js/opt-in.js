/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('submit', function () {
            this.elements['assistance_allowed'].value =
                this.elements['assistance_allowed_checkbox'].checked ?
                    config.allowAccess : config.denyAccess;
        });
    };
});
