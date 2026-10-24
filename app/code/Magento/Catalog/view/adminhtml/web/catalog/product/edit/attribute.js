/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/mage',
    'validation'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).mage('form').validation({
            validationUrl: config.validationUrl
        });
    };
});
