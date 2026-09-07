/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        $(element).text(config.message || 'sri-test-widget loaded');
    };
});
