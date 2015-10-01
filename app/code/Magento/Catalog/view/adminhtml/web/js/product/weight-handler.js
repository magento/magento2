/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {

        /**
         * Hide weight switcher
         */
        hideWeightSwitcher: function () {
            $('[data-role=weight-switcher]').hide();
        }
    };
});
