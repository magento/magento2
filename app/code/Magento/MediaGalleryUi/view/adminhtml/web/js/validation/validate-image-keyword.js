/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/validate',
    'mage/translate'
], function ($, validate, $t) {
    'use strict';

    $.validator.addMethod(
        'validate-image-keyword', function (value) {
            return /^[a-zA-Z0-9\-\_\.\,]+$|^$/i.test(value);

        }, $t('Please use only letters (a-z or A-Z), numbers (0-9), dots (.), commas(,), ' +
            'underscores (_) and dashes(-) on this field.'));
});
