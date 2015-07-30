/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/fotorama'
], function ($, fotorama) {
    'use strict';

    return function (config, element) {

        if ('ontouchstart' in document.documentElement) {
            config.arrows = false;
            config.nav = 'dots';
        }
        $(element).fotorama(config);
    };
});