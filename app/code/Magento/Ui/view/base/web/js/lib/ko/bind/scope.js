/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates scope binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'uiRegistry',
    'jquery',
    'mage/translate'
], function (ko, registry, $) {
    'use strict';

    var i18n = $.mage.__;

    /**
     * Creates child context with passed component param as $data. Extends context with $t helper.
     * Applies bindings to descendant nodes.
     * @param {HTMLElement} el - element to apply bindings to.
     * @param {ko.bindingContext} bindingContext - instance of ko.bindingContext, passed to binding initially.
     * @param {Object} component - component instance to attach to new context
     */
    function applyComponents(el, bindingContext, component) {
        component = bindingContext.createChildContext(component);

        ko.utils.extend(component, {
            $t: i18n
        });

        ko.utils.arrayForEach(el.childNodes, ko.cleanNode);

        ko.applyBindingsToDescendants(component, el);
    }

    ko.bindingHandlers.scope = {

        /**
         * Scope binding's init method.
         * @returns {Object} - Knockout declaration for it to let binding control descendants.
         */
        init: function () {
            return {
                controlsDescendantBindings: true
            };
        },

        /**
         * Reads params passed to binding, parses component declarations.
         * Fetches for those found and attaches them to the new context.
         * @param {HTMLElement} el - Element to apply bindings to.
         * @param {Function} valueAccessor - Function that returns value, passed to binding.
         * @param {Object} allBindings - Object, which represents all bindings applied to element.
         * @param {Object} viewModel - Object, which represents view model binded to el.
         * @param {ko.bindingContext} bindingContext - Instance of ko.bindingContext, passed to binding initially.
         */
        update: function (el, valueAccessor, allBindings, viewModel, bindingContext) {
            var component = valueAccessor(),
                apply = applyComponents.bind(this, el, bindingContext);

            if (typeof component === 'string') {
                registry.get(component, apply);
            } else if (typeof component === 'function') {
                component(apply);
            }
        }
    };

    ko.virtualElements.allowedBindings.scope = true;
});
