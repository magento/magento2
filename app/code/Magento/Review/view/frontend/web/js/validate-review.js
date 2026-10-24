/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'rating-required', function (value) {
            return value !== undefined;
        }, $.mage.__('Please select one of each of the ratings above.'));
});
