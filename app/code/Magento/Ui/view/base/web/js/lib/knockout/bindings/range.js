/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'underscore',
    '../template/renderer'
], function (ko, $, _, renderer) {
    'use strict';

    var isTouchDevice = !_.isUndefined(document.ontouchstart),
        sliderFn = 'slider',
        sliderModule = 'jquery-ui-modules/slider';

    if (isTouchDevice) {
        sliderFn = 'touchSlider';
        sliderModule = 'mage/touch-slider';
    }

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

            _.extend(config, {
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

            require([sliderModule], function () {
                $(element)[sliderFn](config);
            });
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

            require([sliderModule], function () {
                $(element)[sliderFn]('option', config);
            });
        }
    };

    renderer.addAttribute('range');
});
