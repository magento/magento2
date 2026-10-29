/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'validate-image-description', function (value) {
            return /^[a-zA-Z0-9\-\_\.\,\n\s]+$|^$/i.test(value);

        }, $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), ' +
            'dots (.), commas(,), underscores (_), dashes (-), and spaces on this field.'));
});
