/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    '../template/renderer',
    'spectrum'
], function (ko, $, renderer, spectrum) {
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
            $(element).spectrum(config);
        },

        update: function(element, valueAccessor, allBindings, viewModel) {
            var config = valueAccessor();
            $(element).spectrum("set", config.value());
        }
    };

    renderer.addAttribute('colorPicker');
});
