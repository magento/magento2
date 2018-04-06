/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    '../template/renderer',
    'spectrum',
    'tinycolor'
], function (ko, $, renderer, spectrum, tinycolor) {
    'use strict';

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

            if (!viewModel.disabled()) {
                config.change = changeValue;

                config.hide = changeValue;

                /** show value */
                config.show = function () {
                    if (!viewModel.focused()) {
                        viewModel.focused(true);
                    }

                    return true;
                };
                $(element).spectrum(config);
            } else {
                $(element).spectrum({
                    disabled: true
                });
            }
        },

        /**
         * Reads params passed to binding, parses component declarations.
         * Fetches for those found and attaches them to the new context.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        update: function (element, valueAccessor) {
            var config = valueAccessor();

            if (tinycolor(config.value()).isValid() || config.value() === '') {
                $(element).spectrum('set', config.value());

                if (config.value() !== '') {
                    config.value($(element).spectrum('get').toString());
                }
            }
        }
    };

    renderer.addAttribute('colorPicker');
});
