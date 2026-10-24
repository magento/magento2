/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
/*eslint-disable no-undef*/
define(
    ['jquery'],
    function ($) {
        'use strict';

        return function (config, element) {
            $(element).on('click', config, function () {
                confirmSetLocation(config.message, config.url);
            });
        };
    }
);
