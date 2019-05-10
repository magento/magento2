/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
