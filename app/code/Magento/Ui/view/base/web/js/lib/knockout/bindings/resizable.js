/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    '../template/renderer',
    'jquery/ui'
], function (ko, $, async, _, renderer) {
    'use strict';

    var sizeOptions = [
        'minHeight',
        'maxHeight',
        'minWidth',
        'maxWidth'
    ];

    /**
     * Recalcs allowed min, max width and height based on configured selectors
     *
     * @param {Object} event
     * @param {Object} ui
     *
     */
    function recalcAllowedSize(event, ui) {
        var size;

        _.each(this.sizeConstraints, function (selector, key) {
            async.async({
                component: this.ctx,
                selector: selector
            }, function (elem) {
                size = key.includes('Height') ? $(elem).height() : $(elem).width();
                $(ui.element).resizable('option', key, size);
            });
        }, this);
    }

    /**
     * Preprocess config to separate options,
     * which must be processed further before applying
     *
     * @param {Object} config
     * @param {Object} viewModel
     *
     */
    function processConfig(config, viewModel) {
        var sizeConstraint,
            sizeConstraints = {};

        _.each(sizeOptions, function (key) {
            sizeConstraint = config[key];

            if (sizeConstraint) {
                config[key] = _.isNumber(sizeConstraint) ? sizeConstraint :
                    (function () {
                        sizeConstraints[key] = sizeConstraint;

                        return null;
                    })();
            }
        });

        config.start = recalcAllowedSize.bind({
            sizeConstraints: sizeConstraints,
            ctx: viewModel.name
        });
    }

    ko.bindingHandlers.resizable = {

        /**
         * Binding init callback.
         *
         * @param {*} element
         * @param {Function} valueAccessor
         * @param {Function} allBindings
         * @param {Object} viewModel
         *
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var config = valueAccessor();

            if (typeof config === 'object') {
                processConfig(config, viewModel);
                $(element).resizable(config);
            } else {
                $(element).resizable();
            }
        }
    };

    renderer.addAttribute('resizable');
});
