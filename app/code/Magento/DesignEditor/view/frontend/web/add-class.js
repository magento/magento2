/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {

        if (config.config) {
            $(element).addClass(config.class);
        }

        $('body').attr('data-container', 'body');
    };
});
