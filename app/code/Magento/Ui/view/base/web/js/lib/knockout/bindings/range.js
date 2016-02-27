/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    '../template/renderer',
    'jquery/ui'
], function (ko, $, renderer) {
    'use strict';

    ko.bindingHandlers.range = {

        /**
         * Initializes binding and a slider update.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        init: function (element, valueAccessor) {
            var config  = valueAccessor(),
                value   = config.value;

            $.extend(config, {
                value: value(),

                /**
                 * Callback which is being called when sliders' value changes.
                 *
                 * @param {Event} event
                 * @param {Object} ui
                 */
                slide: function (event, ui) {
                    value(ui.value);
                }
            });

            $(element).slider(config);
        },

        /**
         * Updates sliders' plugin configuration.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        update: function (element, valueAccessor) {
            var config = valueAccessor();

            config.value = ko.unwrap(config.value);

            $(element).slider('option', config);
        }
    };

    renderer.addAttribute('range');
});
