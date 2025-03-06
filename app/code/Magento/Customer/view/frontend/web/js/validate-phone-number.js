/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define(['jquery', 'mage/validation'], function ($) {
    'use strict';

    return function () {
        $.validator.addMethod(
            'validate-phone-number',
            function (value) {
                return /^[0-9+\-()\s]+$/.test(value); // Allows numbers, +, -, (), and spaces
            },
            $.mage.__('Invalid phone number. Please use 0-9, +, -, (, ) and space.')
        );
    };
});
