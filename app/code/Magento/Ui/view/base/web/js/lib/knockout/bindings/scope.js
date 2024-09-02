/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates scope binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'uiRegistry',
    'mage/translate',
    '../template/renderer',
    'jquery',
    '../../logger/console-logger'
], function (ko, registry, $t, renderer, $, consoleLogger) {
    'use strict';

    /**
     * Check if an element is visible in the viewport
     *
     * @param {HTMLElement} el
     * @returns bool
     */
    function isInViewport(el) {
        if (!el.checkVisibility()) return false;
        const rect = el.getBoundingClientRect();
        const vWidth = window.innerWidth || doc.documentElement.clientWidth;
        const vHeight = window.innerHeight || doc.documentElement.clientHeight;

        // Check if the element is out of bounds
        if (rect.right < 0 || rect.bottom < 0 || rect.left > vWidth || rect.top > vHeight) return false;

        // Return true if any of the above disjunctions are false
        return true;
    }

    /**
     *  Check condition apply components
     *
     * @param {HTMLElement} el - element to apply bindings to.
     * @param {ko.bindingContext} bindingContext - instance of ko.bindingContext, passed to binding initially.
     * @param {Promise} promise - instance of jQuery promise
     * @param {Object} component - component instance to attach to new context
     */
    function applyComponents(el, bindingContext, promise, component) {
        if (isInViewport(el)) {
            runApplyComponents(el, bindingContext, promise, component);
        } else {
            document.addEventListener('mousemove', function (e) {
                runApplyComponents(el, bindingContext, promise, component);
            }, { once: true });
        }
    }

    /**
     * Creates child context with passed component param as $data. Extends context with $t helper.
     * Applies bindings to descendant nodes.
     * @param {HTMLElement} el - element to apply bindings to.
     * @param {ko.bindingContext} bindingContext - instance of ko.bindingContext, passed to binding initially.
     * @param {Promise} promise - instance of jQuery promise
     * @param {Object} component - component instance to attach to new context
     */
    function runApplyComponents(el, bindingContext, promise, component) {
        promise.resolve();
        component = bindingContext.createChildContext(component);

        ko.utils.extend(component, {
            $t: $t
        });

        ko.utils.arrayForEach(ko.virtualElements.childNodes(el), ko.cleanNode);

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
                promise = $.Deferred(),
                apply = applyComponents.bind(this, el, bindingContext, promise),
                loggerUtils = consoleLogger.utils;

            if (typeof component === 'string') {
                loggerUtils.asyncLog(
                    promise,
                    {
                        data: {
                            component: component
                        },
                        messages: loggerUtils.createMessages(
                            'requestingComponent',
                            'requestingComponentIsLoaded',
                            'requestingComponentIsFailed'
                        )
                    }
                );

                registry.get(component, apply);
            } else if (typeof component === 'function') {
                component(apply);
            }
        }
    };

    ko.virtualElements.allowedBindings.scope = true;

    renderer
        .addNode('scope')
        .addAttribute('scope', {
            name: 'ko-scope'
        });
});
