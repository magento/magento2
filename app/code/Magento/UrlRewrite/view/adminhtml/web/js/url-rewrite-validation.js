/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).form().validation({
            validationUrl: data.url
        });
    };
});
