/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.1.3): dom/selector-engine.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

define([
    "../util/index"
], function(Util) {
    'use strict';

    const isDisabled = Util.isDisabled;
    const isVisible = Util.isVisible;

    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */

    const NODE_TEXT = 3

    return {
        find: function(selector, element = document.documentElement) {
            return [].concat(...Element.prototype.querySelectorAll.call(element, selector))
        },

        findOne: function(selector, element = document.documentElement) {
            return Element.prototype.querySelector.call(element, selector)
        },

        children: function(element, selector) {
            return [].concat(...element.children)
                .filter(child => child.matches(selector))
        },

        parents: function(element, selector) {
            const parents = []

            let ancestor = element.parentNode

            while (ancestor && ancestor.nodeType === Node.ELEMENT_NODE && ancestor.nodeType !== NODE_TEXT) {
                if (ancestor.matches(selector)) {
                    parents.push(ancestor)
                }

                ancestor = ancestor.parentNode
            }

            return parents
        },

        prev: function(element, selector) {
            let previous = element.previousElementSibling

            while (previous) {
                if (previous.matches(selector)) {
                    return [previous]
                }

                previous = previous.previousElementSibling
            }

            return []
        },

        next: function(element, selector) {
            let next = element.nextElementSibling

            while (next) {
                if (next.matches(selector)) {
                    return [next]
                }

                next = next.nextElementSibling
            }

            return []
        },

        focusableChildren: function(element) {
            const focusables = [
                'a',
                'button',
                'input',
                'textarea',
                'select',
                'details',
                '[tabindex]',
                '[contenteditable="true"]'
            ].map(selector => `${selector}:not([tabindex^="-"])`).join(', ')

            return this.find(focusables, element).filter(el => !isDisabled(el) && isVisible(el))
        }
    }
});
