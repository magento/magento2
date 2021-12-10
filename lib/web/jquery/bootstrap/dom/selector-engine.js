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

    var isDisabled = Util.isDisabled;
    var isVisible = Util.isVisible;

    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */

    var NODE_TEXT = 3

    return {
        find: function(selector, element = document.documentElement) {
            return [].concat(...Element.prototype.querySelectorAll.call(element, selector))
        },

        findOne: function(selector, element = document.documentElement) {
            return Element.prototype.querySelector.call(element, selector)
        },

        children: function(element, selector) {
            return [].concat(...element.children)
                .filter(function(child) {
                    return child.matches(selector)
                })
        },

        parents: function(element, selector) {
            var parents = []

            var ancestor = element.parentNode

            while (ancestor && ancestor.nodeType === Node.ELEMENT_NODE && ancestor.nodeType !== NODE_TEXT) {
                if (ancestor.matches(selector)) {
                    parents.push(ancestor)
                }

                ancestor = ancestor.parentNode
            }

            return parents
        },

        prev: function(element, selector) {
            var previous = element.previousElementSibling

            while (previous) {
                if (previous.matches(selector)) {
                    return [previous]
                }

                previous = previous.previousElementSibling
            }

            return []
        },

        next: function(element, selector) {
            var next = element.nextElementSibling

            while (next) {
                if (next.matches(selector)) {
                    return [next]
                }

                next = next.nextElementSibling
            }

            return []
        },

        focusableChildren: function(element) {
            var focusables = [
                'a',
                'button',
                'input',
                'textarea',
                'select',
                'details',
                '[tabindex]',
                '[contenteditable="true"]'
            ].map(function(selector) {
                return `${selector}:not([tabindex^="-"])`
            }).join(', ')

            return this.find(focusables, element).filter(function(el) {
                return !isDisabled(el) && isVisible(el)
            })
        }
    }
});
