/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, element) {

        $(element).mage('form').mage('validation', {
            validationUrl: config.validationUrl
        });
    };
});
