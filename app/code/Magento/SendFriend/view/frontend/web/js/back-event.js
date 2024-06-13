/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function () {
            history.back();

            return false;
        });
    };
});
