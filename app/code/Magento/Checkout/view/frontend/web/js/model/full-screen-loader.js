/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['jquery'],
    function ($) {
        'use strict';

        var containerId = '#checkout';

        return {

            /**
             * Start full page loader action
             */
            startLoader: function () {
                $(containerId).trigger('processStart');
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                $(containerId).trigger('processStop');
            }
        };
    }
);
