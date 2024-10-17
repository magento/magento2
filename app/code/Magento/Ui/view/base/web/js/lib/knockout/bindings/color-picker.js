/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    '../template/renderer'
], function (ko, $, renderer) {
    'use strict';

    /**
     * Change color picker status to be enabled or disabled
     *
     * @param {HTMLElement} element - Element to apply colorpicker enable/disable status to.
     * @param {Object} viewModel - Object, which represents view model binded to el.
     */
    function changeColorPickerStateBasedOnViewModel(element, viewModel) {
        $(element).spectrum(viewModel.disabled() ? 'disable' : 'enable');
    }

    ko.bindingHandlers.colorPicker = {
        /**
         * Binding init callback.
         *
         * @param {*} element
         * @param {Function} valueAccessor
         * @param {Function} allBindings
         * @param {Object} viewModel
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var config = valueAccessor(),

                /** change value */
                changeValue = function (value) {
                    if (value == null) {
                        value = '';
                    }
                    config.value(value.toString());
                };

            config.change = changeValue;

            config.hide = changeValue;

            /** show value */
            config.show = function () {
                if (!viewModel.focused()) {
                    viewModel.focused(true);
                }

                return true;
            };

            require(['tinycolor', 'spectrum'], function () {
                $(element).spectrum(config);

                changeColorPickerStateBasedOnViewModel(element, viewModel);
            });
        },

        /**
         * Reads params passed to binding, parses component declarations.
         * Fetches for those found and attaches them to the new context.
         *
         * @param {HTMLElement} element - Element to apply bindings to.
         * @param {Function} valueAccessor - Function that returns value, passed to binding.
         * @param {Object} allBindings - Object, which represents all bindings applied to element.
         * @param {Object} viewModel - Object, which represents view model binded to element.
         */
        update: function (element, valueAccessor, allBindings, viewModel) {
            var config = valueAccessor();

            /** Initialise value as empty if it is undefined when color picker input is reset **/
            if (config.value() === undefined) {
                config.value('');
            }

            require(['tinycolor', 'spectrum'], function (tinycolor) {
                if (tinycolor(config.value()).isValid() || config.value() === '') {
                    $(element).spectrum('set', config.value());

                    if (config.value() !== '') {
                        config.value($(element).spectrum('get').toString());
                    }
                }

                changeColorPickerStateBasedOnViewModel(element, viewModel);
            });
        }
    };

    renderer.addAttribute('colorPicker');
});
