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

    var collapsible,
        defaults;

    defaults = {
        closeOnOuter: true,
        onTarget: false,
        openClass: '_active',
        as: '$collapsible'
    };

    collapsible = {

        /**
         * Sets 'opened' property to true.
         */
        open: function () {
            this.opened(true);
        },

        /**
         * Sets 'opened' property to false.
         */
        close: function () {
            this.opened(false);
        },

        /**
         * Toggles value of the 'opened' property.
         */
        toggle: function () {
            this.opened(!this.opened());
        }
    };

    /**
     * Document click handler which in case if event target is not
     * a descendant of provided container element, closes collapsible model.
     *
     * @param {HTMLElement} container
     * @param {Object} model
     * @param {EventObject} e
     */
    function onOuterClick(container, model, e) {
        var target = e.target;

        if (target !== container && !container.contains(target)) {
            model.close();
        }
    }

    /**
     * Creates 'css' binding which toggles
     * class specified in 'name' parameter.
     *
     * @param {Object} model
     * @param {String} name
     * @returns {Object}
     */
    function getClassBinding(model, name) {
        var binding = {};

        binding[name] = model.opened;

        return {
            css: binding
        };
    }

    /**
     * Prepares configuration for the binding based
     * on a default properties and provided options.
     *
     * @param {Object} [options={}]
     * @returns {Object} Complete instance configuration.
     */
    function buildConfig(options) {
        if (typeof options !== 'object') {
            options = {};
        }

        return _.extend({}, defaults, options);
    }

    ko.bindingHandlers.collapsible = {

        /**
         * Initializes 'collapsible' binding.
         */
        init: function (element, valueAccessor, allBindings, viewModel, bindingCtx) {
            var $collapsible = Object.create(collapsible),
                config = buildConfig(valueAccessor()),
                outerClick,
                bindings;

            _.bindAll($collapsible, 'open', 'close', 'toggle');

            $collapsible.opened = ko.observable(!!config.opened);

            bindingCtx[config.as] = $collapsible;

            if (config.closeOnOuter) {
                outerClick = onOuterClick.bind(null, element, $collapsible);

                $(document).on('click', outerClick);

                ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                    $(document).off('click', outerClick);
                });
            }

            if (config.openClass) {
                bindings = getClassBinding($collapsible, config.openClass);

                ko.applyBindingsToNode(element, bindings, bindingCtx);
            }

            if (config.onTarget) {
                $(element).on('click', $collapsible.toggle);
            }

            if (viewModel && _.isFunction(viewModel.on)) {
                viewModel.on({
                    close:          $collapsible.close,
                    open:           $collapsible.open,
                    toggleOpened:   $collapsible.toggle
                });
            }
        }
    };

    ko.bindingHandlers.closeCollapsible = {

        /**
         * Creates listener for the click event on provided DOM element,
         * which closes associated with it collapsible model.
         */
        init: function (element, valueAccessor, allBindings, viewModel, bindingCtx) {
            var name = valueAccessor() || defaults.as,
                $collapsible = bindingCtx[name];

            if ($collapsible) {
                $(element).on('click', $collapsible.close);
            }
        }
    };

    ko.bindingHandlers.openCollapsible = {

        /**
         * Creates listener for the click event on provided DOM element,
         * which opens associated with it collapsible model.
         */
        init: function (element, valueAccessor, allBindings, viewModel, bindingCtx) {
            var name = valueAccessor() || defaults.as,
                $collapsible = bindingCtx[name];

            if ($collapsible) {
                $(element).on('click', $collapsible.open);
            }
        }
    };

    ko.bindingHandlers.toggleCollapsible = {

        /**
         * Creates listener for the click event on provided DOM element,
         * which toggles associated with it collapsible model.
         */
        init: function (element, valueAccessor, allBindings, viewModel, bindingCtx) {
            var name = valueAccessor() || defaults.as,
                $collapsible = bindingCtx[name];

            if ($collapsible) {
                $(element).on('click', $collapsible.toggle);
            }
        }
    };

    renderer
        .addAttribute('collapsible')
        .addAttribute('openCollapsible')
        .addAttribute('closeCollapsible')
        .addAttribute('toggleCollapsible');
});
