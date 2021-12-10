/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.1.3): util/index.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */


define([
    "jquery",
    'domReady!'
], function() {
    'use strict';

    var MAX_UID = 1000000
    var MILLISECONDS_MULTIPLIER = 1000
    var TRANSITION_END = 'transitionend'

    // Shoutout AngusCroll (https://goo.gl/pxwQGp)
    var toType = function (obj) {
        if (obj === null || obj === undefined) {
            return `${obj}`
        }

        return {}.toString.call(obj).match(/\s([a-z]+)/i)[1].toLowerCase()
    }

    /**
     * --------------------------------------------------------------------------
     * Public Util Api
     * --------------------------------------------------------------------------
     */

    var getUID = function (prefix) {
        do {
            prefix += Math.floor(Math.random() * MAX_UID)
        } while (document.getElementById(prefix))

        return prefix
    }

    var getSelector = function (element) {
        var selector = element.getAttribute('data-bs-target')

        if (!selector || selector === '#') {
            var hrefAttr = element.getAttribute('href')

            // The only valid content that could double as a selector are IDs or classes,
            // so everything starting with `#` or `.`. If a "real" URL is used as the selector,
            // `document.querySelector` will rightfully complain it is invalid.
            // See https://github.com/twbs/bootstrap/issues/32273
            if (!hrefAttr || (!hrefAttr.includes('#') && !hrefAttr.startsWith('.'))) {
                return null
            }

            // Just in case some CMS puts out a full URL with the anchor appended
            if (hrefAttr.includes('#') && !hrefAttr.startsWith('#')) {
                hrefAttr = `#${hrefAttr.split('#')[1]}`
            }

            selector = hrefAttr && hrefAttr !== '#' ? hrefAttr.trim() : null
        }

        return selector
    }

    var getSelectorFromElement = function (element) {
        var selector = getSelector(element)

        if (selector) {
            return document.querySelector(selector) ? selector : null
        }

        return null
    }

    var getElementFromSelector = function (element) {
        var selector = getSelector(element)

        return selector ? document.querySelector(selector) : null
    }

    var getTransitionDurationFromElement = function (element) {
        if (!element) {
            return 0
        }

        // Get transition-duration of the element
        var {transitionDuration, transitionDelay} = window.getComputedStyle(element)

        var floatTransitionDuration = Number.parseFloat(transitionDuration)
        var floatTransitionDelay = Number.parseFloat(transitionDelay)

        // Return 0 if element or transition duration is not found
        if (!floatTransitionDuration && !floatTransitionDelay) {
            return 0
        }

        // If multiple durations are defined, take the first
        transitionDuration = transitionDuration.split(',')[0]
        transitionDelay = transitionDelay.split(',')[0]

        return (Number.parseFloat(transitionDuration) + Number.parseFloat(transitionDelay)) * MILLISECONDS_MULTIPLIER
    }

    var triggerTransitionEnd = function (element) {
        element.dispatchEvent(new Event(TRANSITION_END))
    }

    var isElement = function (obj) {
        if (!obj || typeof obj !== 'object') {
            return false
        }

        if (typeof obj.jquery !== 'undefined') {
            obj = obj[0]
        }

        return typeof obj.nodeType !== 'undefined'
    }

    var getElement = function (obj) {
        if (isElement(obj)) { // it's a jQuery object or a node element
            return obj.jquery ? obj[0] : obj
        }

        if (typeof obj === 'string' && obj.length > 0) {
            return document.querySelector(obj)
        }

        return null
    }

    var typeCheckConfig = function (componentName, config, configTypes) {
        Object.keys(configTypes).forEach(function(property) {
            var expectedTypes = configTypes[property]
            var value = config[property]
            var valueType = value && isElement(value) ? 'element' : toType(value)

            if (!new RegExp(expectedTypes).test(valueType)) {
                throw new TypeError(
                    `${componentName.toUpperCase()}: Option "${property}" provided type "${valueType}" but expected type "${expectedTypes}".`
                )
            }
        })
    }

    var isVisible = function (element) {
        if (!isElement(element) || element.getClientRects().length === 0) {
            return false
        }

        return getComputedStyle(element).getPropertyValue('visibility') === 'visible'
    }

    var isDisabled = function (element) {
        if (!element || element.nodeType !== Node.ELEMENT_NODE) {
            return true
        }

        if (element.classList.contains('disabled')) {
            return true
        }

        if (typeof element.disabled !== 'undefined') {
            return element.disabled
        }

        return element.hasAttribute('disabled') && element.getAttribute('disabled') !== 'false'
    }

    var findShadowRoot = function (element) {
        if (!document.documentElement.attachShadow) {
            return null
        }

        // Can find the shadow root otherwise it'll return the document
        if (typeof element.getRootNode === 'function') {
            var root = element.getRootNode()
            return root instanceof ShadowRoot ? root : null
        }

        if (element instanceof ShadowRoot) {
            return element
        }

        // when we don't find a shadow root
        if (!element.parentNode) {
            return null
        }

        return findShadowRoot(element.parentNode)
    }

    var noop = function () {
    }

    /**
     * Trick to restart an element's animation
     *
     * @param {HTMLElement} element
     * @return void
     *
     * @see https://www.charistheo.io/blog/2021/02/restart-a-css-animation-with-javascript/#restarting-a-css-animation
     */
    var reflow = function (element) {
        // eslint-disable-next-line no-unused-expressions
        element.offsetHeight
    }

    var getjQuery = function () {
        var {jQuery} = window

        if (jQuery && !document.body.hasAttribute('data-bs-no-jquery')) {
            return jQuery
        }

        return null
    }

    var DOMContentLoadedCallbacks = []

    var onDOMContentLoaded = function (callback) {
        if (document.readyState === 'loading') {
            // add listener on the first call when the document is in loading state
            if (!DOMContentLoadedCallbacks.length) {
                document.addEventListener('DOMContentLoaded', function() {
                    DOMContentLoadedCallbacks.forEach(function(callback) {
                        return callback()
                    })
                })
            }

            DOMContentLoadedCallbacks.push(callback)
        } else {
            callback()
        }
    }

    var isRTL = function () {
        return document.documentElement.dir === 'rtl'
    }

    var defineJQueryPlugin = function (plugin) {
        onDOMContentLoaded(function () {
            var $ = getjQuery()
            /* istanbul ignore if */
            if ($) {
                var name = plugin.NAME
                var JQUERY_NO_CONFLICT = $.fn[name]
                $.fn[name] = plugin.jQueryInterface
                $.fn[name].Constructor = plugin
                $.fn[name].noConflict = function() {
                    $.fn[name] = JQUERY_NO_CONFLICT
                    return plugin.jQueryInterface
                }
            }
        })
    }

    var execute = function (callback) {
        if (typeof callback === 'function') {
            callback()
        }
    }

    var executeAfterTransition = function (callback, transitionElement, waitForTransition = true) {
        if (!waitForTransition) {
            execute(callback)
            return
        }

        var durationPadding = 5
        var emulatedDuration = getTransitionDurationFromElement(transitionElement) + durationPadding

        var called = false

        var handler = function ({target}) {
            if (target !== transitionElement) {
                return
            }

            called = true
            transitionElement.removeEventListener(TRANSITION_END, handler)
            execute(callback)
        }

        transitionElement.addEventListener(TRANSITION_END, handler)
        setTimeout(function() {
            if (!called) {
                triggerTransitionEnd(transitionElement)
            }
        }, emulatedDuration)
    }

    /**
     * Return the previous/next element of a list.
     *
     * @param {array} list    The list of elements
     * @param activeElement   The active element
     * @param shouldGetNext   Choose to get next or previous element
     * @param isCycleAllowed
     * @return {Element|elem} The proper element
     */
    var getNextActiveElement = function (list, activeElement, shouldGetNext, isCycleAllowed) {
        var index = list.indexOf(activeElement)

        // if the element does not exist in the list return an element depending on the direction and if cycle is allowed
        if (index === -1) {
            return list[!shouldGetNext && isCycleAllowed ? list.length - 1 : 0]
        }

        var listLength = list.length

        index += shouldGetNext ? 1 : -1

        if (isCycleAllowed) {
            index = (index + listLength) % listLength
        }

        return list[Math.max(0, Math.min(index, listLength - 1))]
    }

    return {
        getElement,
        getUID,
        getSelectorFromElement,
        getElementFromSelector,
        getTransitionDurationFromElement,
        triggerTransitionEnd,
        isElement,
        typeCheckConfig,
        isVisible,
        isDisabled,
        findShadowRoot,
        noop,
        getNextActiveElement,
        reflow,
        getjQuery,
        onDOMContentLoaded,
        isRTL,
        defineJQueryPlugin,
        execute,
        executeAfterTransition
    };
});
