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
            var config = valueAccessor();

            if (!viewModel.disabled()) {
                config.change = function (value) {
                    if (value == null) {
                        value = '';
                    }
                    config.value(value.toString());
                };

                config.hide = function (value) {
                    if (value == null) {
                        value = '';
                    }
                    config.value(value.toString());
                };
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

        update: function(element, valueAccessor) {
            var config = valueAccessor();

            if (tinycolor(config.value()).isValid() || config.value() === '') {
                $(element).spectrum("set", config.value());
            }
        }
    };

    renderer.addAttribute('colorPicker');
});
