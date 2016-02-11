/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore',
    '../template/renderer',
    'jquery/ui'
], function (ko, $, _, renderer) {
    'use strict';

    /**
     * Recalcs allowed min, max width and height based on configured selectors
     *
     * @param {Object} event
     * @param {Object} ui
     *
     */
    function recalcAllowedSize(event, ui) {
        var size;

        _.each(this, function (constraints, key) {
            _.each(constraints, function (selector, constraint) {
                size = key === 'height' ? $(selector).height() : $(selector).width();
                $(ui.element).resizable('option', constraint, size);
            });
        });
    }

    ko.bindingHandlers.resizable = {

        /**
         * Binding init callback.
         *
         * @param {*} element
         *
         */
        init: function (element, valueAccessor) {
            var config = valueAccessor();

            if (config.sizeConstraints) {
                config.options.start = recalcAllowedSize.bind(config.sizeConstraints);
            }

            if (typeof config.options === 'object') {
                $(element).resizable(config.options);
            }
        }
    };

    renderer.addAttribute('resizable');
});
