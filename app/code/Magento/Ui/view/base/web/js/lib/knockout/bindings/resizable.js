/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'underscore',
    '../template/renderer',
    'jquery/ui'
], function (ko, $, async, registry, _, renderer) {
    'use strict';

    var sizeOptions = [
            'minHeight',
            'maxHeight',
            'minWidth',
            'maxWidth'
        ],

        handles = {
            height: '.ui-resizable-s, .ui-resizable-n',
            width: '.ui-resizable-w, .ui-resizable-e'
        };

    /**
     * Recalcs visibility of handles, width and height of resizable based on content
     * @param {HTMLElement} element
     */
    function adjustSize(element) {
        var maxHeight,
            maxWidth;

        element = $(element);
        maxHeight = element.resizable('option').maxHeight;
        maxWidth = element.resizable('option').maxWidth;

        if (maxHeight && element.height() > maxHeight) {
            element.height(maxHeight + 1);
            $(handles.height).hide();
        } else {
            $(handles.height).show();
        }

        if (maxWidth && $(element).width() > maxWidth) {
            element.width(maxWidth + 1);
            $(handles.width).hide();
        } else {
            $(handles.width).show();
        }
    }

    /**
     * Recalcs allowed min, max width and height based on configured selectors
     */
    function recalcAllowedSize() {
        var size,
            element = this.element;

        if (_.isEmpty(this.widthUpdate)) {
            $(element).css('width', 'auto');
        }

        _.each(this.sizeConstraints, function (selector, key) {
            async.async({
                component: this.componentName,
                selector: selector
            }, function (elem) {
                size = key.indexOf('Height') !== -1 ? $(elem).outerHeight(true) : $(elem).outerWidth(true);
                $(element).resizable('option', key, size + 1);
            });
        }, this);

        adjustSize(element);
    }

    /**
     * Preprocess config to separate options,
     * which must be processed further before applying
     *
     * @param {Object} config
     * @param {Object} viewModel
     * @param {*} element
     * @return {Object} config
     */
    function processConfig(config, viewModel, element) {
        var sizeConstraint,
            sizeConstraints = {},
            recalc;

        if (_.isEmpty(config)) {
            return {};
        }
        _.each(sizeOptions, function (key) {
            sizeConstraint = config[key];

            if (sizeConstraint && !_.isNumber(sizeConstraint)) {
                sizeConstraints[key] = sizeConstraint;
                delete config[key];
            }
        });

        recalc = recalcAllowedSize.bind({
            sizeConstraints: sizeConstraints,
            componentName: viewModel.name,
            element: element,
            widthUpdate: _.filter(sizeConstraints, function (value, key) {
                return key.indexOf('Width') !== -1;
            })
        });
        config.start = recalc;
        $(window).on('resize.resizable', recalc);
        registry.get(viewModel.provider).on('reloaded', recalc);

        return config;
    }

    ko.bindingHandlers.resizable = {

        /**
         * Binding init callback.
         *
         * @param {*} element
         * @param {Function} valueAccessor
         * @param {Function} allBindings
         * @param {Object} viewModel
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var config = processConfig(valueAccessor(), viewModel, element);

            $(element).resizable(config);
        }
    };

    renderer.addAttribute('resizable');
});
