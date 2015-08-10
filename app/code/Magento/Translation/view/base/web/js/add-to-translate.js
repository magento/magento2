/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return function (config) {
        require([config.config], function (string) {
            if (string.length) {
                $.mage.translate.add(JSON.parse(string));
            }
        });
    };
});
