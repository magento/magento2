/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        $(element).addClass(config.class);
    };
});
