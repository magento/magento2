/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'validate-image-title', function (value) {
            return /^[a-zA-Z0-9\-\_\.\,\ ]+$/i.test(value);

        }, $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), dots (.), commas(,), ' +
            'underscores (_), dashes(-) and spaces on this field.'));
});
