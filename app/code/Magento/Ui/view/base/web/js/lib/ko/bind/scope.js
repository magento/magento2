/**
 * @category    storage
 * @package     test
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates scope binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'Magento_Ui/js/lib/registry/registry',
    'jquery',
    'mage/translate'
], function(ko, registry, $) {
    'use strict';

    var i18n = $.mage.__;

    /**
     * Fetches components from registry and stores them to context object, then passes it to callback function.
     * @param {Object} components - map, representing components to be attached to the new context.
     * @param {Function} callback - Function to be called when components are fetched.
     */
    function getMultiple(components, callback) {
        var key,
            paths = [],
            context = {};

        for (key in components) {
            paths.push(components[key]);
        }

        registry.get(paths, function() {

            for (key in components) {
                context[key] = registry.get(components[key]);
            }

            callback(context);
        });
    }

    /**
     * Creates child context with passed component param as $data. Extends context with $t helper.
     * Applies bindings to descendant nodes.
     * @param {HTMLElement} el - element to apply bindings to.
     * @param {ko.bindingContext} bindingContext - instance of ko.bindingContext, passed to binding initially.
     * @param {Object} component - component instance to attach to new context
     */
    function applyComponents(el, bindingContext, component) {
        component = bindingContext.createChildContext(component);
        
        ko.utils.extend(component, { $t: i18n });

        ko.utils.arrayForEach(el.childNodes, ko.cleanNode);
        
        ko.applyBindingsToDescendants(component, el);
    }

    ko.bindingHandlers.scope = {

        /**
         * Scope binding's init method.
         * @returns {Object} - Knockout declaration for it to let binding control descendants.
         */
        init: function () {
            return { controlsDescendantBindings: true };
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
        update: function(el, valueAccessor, allBindings, viewModel, bindingContext) {
            var component = valueAccessor(),
                apply = applyComponents.bind(this, el, bindingContext);

            typeof component === 'object' ?
                getMultiple(component, apply) :
                registry.get(component, apply);
        }
    };
});