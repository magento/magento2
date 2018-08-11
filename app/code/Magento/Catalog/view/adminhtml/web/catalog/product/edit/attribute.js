/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function (jQuery) {
    'use strict';

    return function (config, element) {

        jQuery(element).mage('form').mage('validation', {
            validationUrl: config.validationUrl
        });
    };
});
