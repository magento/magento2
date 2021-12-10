/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.1.3): dom/event-handler.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

define([
    "../util/index"
], function(Util) {
    'use strict';

    var getjQuery = Util.getjQuery;

    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */

    var namespaceRegex = /[^.]*(?=\..*)\.|.*/
    var stripNameRegex = /\..*/
    var stripUidRegex = /::\d+$/
    var eventRegistry = {} // Events storage
    var uidEvent = 1
    var customEvents = {
        mouseenter: 'mouseover',
        mouseleave: 'mouseout'
    }
    var customEventsRegex = /^(mouseenter|mouseleave)/i
    var nativeEvents = new Set([
        'click',
        'dblclick',
        'mouseup',
        'mousedown',
        'contextmenu',
        'mousewheel',
        'DOMMouseScroll',
        'mouseover',
        'mouseout',
        'mousemove',
        'selectstart',
        'selectend',
        'keydown',
        'keypress',
        'keyup',
        'orientationchange',
        'touchstart',
        'touchmove',
        'touchend',
        'touchcancel',
        'pointerdown',
        'pointermove',
        'pointerup',
        'pointerleave',
        'pointercancel',
        'gesturestart',
        'gesturechange',
        'gestureend',
        'focus',
        'blur',
        'change',
        'reset',
        'select',
        'submit',
        'focusin',
        'focusout',
        'load',
        'unload',
        'beforeunload',
        'resize',
        'move',
        'DOMContentLoaded',
        'readystatechange',
        'error',
        'abort',
        'scroll'
    ])

    /**
     * ------------------------------------------------------------------------
     * Private methods
     * ------------------------------------------------------------------------
     */

    function getUidEvent(element, uid) {
        return (uid && `${uid}::${uidEvent++}`) || element.uidEvent || uidEvent++
    }

    function getEvent(element) {
        var uid = getUidEvent(element)

        element.uidEvent = uid
        eventRegistry[uid] = eventRegistry[uid] || {}

        return eventRegistry[uid]
    }

    function bootstrapHandler(element, fn) {
        return function handler(event) {
            event.delegateTarget = element

            if (handler.oneOff) {
                EventHandler.off(element, event.type, fn)
            }

            return fn.apply(element, [event])
        }
    }

    function bootstrapDelegationHandler(element, selector, fn) {
        return function handler(event) {
            var domElements = element.querySelectorAll(selector)

            for (var {target} = event; target && target !== this; target = target.parentNode) {
                for (var i = domElements.length; i--;) {
                    if (domElements[i] === target) {
                        event.delegateTarget = target

                        if (handler.oneOff) {
                            EventHandler.off(element, event.type, selector, fn)
                        }

                        return fn.apply(target, [event])
                    }
                }
            }

            // To please ESLint
            return null
        }
    }

    function findHandler(events, handler, delegationSelector = null) {
        var uidEventList = Object.keys(events)

        for (var i = 0, len = uidEventList.length; i < len; i++) {
            var event = events[uidEventList[i]]

            if (event.originalHandler === handler && event.delegationSelector === delegationSelector) {
                return event
            }
        }

        return null
    }

    function normalizeParams(originalTypeEvent, handler, delegationFn) {
        var delegation = typeof handler === 'string'
        var originalHandler = delegation ? delegationFn : handler

        var typeEvent = getTypeEvent(originalTypeEvent)
        var isNative = nativeEvents.has(typeEvent)

        if (!isNative) {
            typeEvent = originalTypeEvent
        }

        return [delegation, originalHandler, typeEvent]
    }

    function addHandler(element, originalTypeEvent, handler, delegationFn, oneOff) {
        if (typeof originalTypeEvent !== 'string' || !element) {
            return
        }

        if (!handler) {
            handler = delegationFn
            delegationFn = null
        }

        // in case of mouseenter or mouseleave wrap the handler within a function that checks for its DOM position
        // this prevents the handler from being dispatched the same way as mouseover or mouseout does
        if (customEventsRegex.test(originalTypeEvent)) {
            var wrapFn = function(fn) {
                return function (event) {
                    if (!event.relatedTarget || (event.relatedTarget !== event.delegateTarget && !event.delegateTarget.contains(event.relatedTarget))) {
                        return fn.call(this, event)
                    }
                }
            }

            if (delegationFn) {
                delegationFn = wrapFn(delegationFn)
            } else {
                handler = wrapFn(handler)
            }
        }

        var [delegation, originalHandler, typeEvent] = normalizeParams(originalTypeEvent, handler, delegationFn)
        var events = getEvent(element)
        var handlers = events[typeEvent] || (events[typeEvent] = {})
        var previousFn = findHandler(handlers, originalHandler, delegation ? handler : null)

        if (previousFn) {
            previousFn.oneOff = previousFn.oneOff && oneOff

            return
        }

        var uid = getUidEvent(originalHandler, originalTypeEvent.replace(namespaceRegex, ''))
        var fn = delegation ?
            bootstrapDelegationHandler(element, handler, delegationFn) :
            bootstrapHandler(element, handler)

        fn.delegationSelector = delegation ? handler : null
        fn.originalHandler = originalHandler
        fn.oneOff = oneOff
        fn.uidEvent = uid
        handlers[uid] = fn

        element.addEventListener(typeEvent, fn, delegation)
    }

    function removeHandler(element, events, typeEvent, handler, delegationSelector) {
        var fn = findHandler(events[typeEvent], handler, delegationSelector)

        if (!fn) {
            return
        }

        element.removeEventListener(typeEvent, fn, Boolean(delegationSelector))
        delete events[typeEvent][fn.uidEvent]
    }

    function removeNamespacedHandlers(element, events, typeEvent, namespace) {
        var storeElementEvent = events[typeEvent] || {}

        Object.keys(storeElementEvent).forEach(function(handlerKey) {
            if (handlerKey.includes(namespace)) {
                var event = storeElementEvent[handlerKey]

                removeHandler(element, events, typeEvent, event.originalHandler, event.delegationSelector)
            }
        })
    }

    function getTypeEvent(event) {
        // allow to get the native events from namespaced events ('click.bs.button' --> 'click')
        event = event.replace(stripNameRegex, '')
        return customEvents[event] || event
    }

    return {
        on: function(element, event, handler, delegationFn) {
            addHandler(element, event, handler, delegationFn, false)
        },

        one: function(element, event, handler, delegationFn) {
            addHandler(element, event, handler, delegationFn, true)
        },

        off: function(element, originalTypeEvent, handler, delegationFn) {
            if (typeof originalTypeEvent !== 'string' || !element) {
                return
            }

            var [delegation, originalHandler, typeEvent] = normalizeParams(originalTypeEvent, handler, delegationFn)
            var inNamespace = typeEvent !== originalTypeEvent
            var events = getEvent(element)
            var isNamespace = originalTypeEvent.startsWith('.')

            if (typeof originalHandler !== 'undefined') {
                // Simplest case: handler is passed, remove that listener ONLY.
                if (!events || !events[typeEvent]) {
                    return
                }

                removeHandler(element, events, typeEvent, originalHandler, delegation ? handler : null)
                return
            }

            if (isNamespace) {
                Object.keys(events).forEach(function(elementEvent) {
                    removeNamespacedHandlers(element, events, elementEvent, originalTypeEvent.slice(1))
                })
            }

            var storeElementEvent = events[typeEvent] || {}
            Object.keys(storeElementEvent).forEach(function(keyHandlers) {
                var handlerKey = keyHandlers.replace(stripUidRegex, '')

                if (!inNamespace || originalTypeEvent.includes(handlerKey)) {
                    var event = storeElementEvent[keyHandlers]

                    removeHandler(element, events, typeEvent, event.originalHandler, event.delegationSelector)
                }
            })
        },

        trigger: function(element, event, args) {
            if (typeof event !== 'string' || !element) {
                return null
            }

            var $ = getjQuery()
            var typeEvent = getTypeEvent(event)
            var inNamespace = event !== typeEvent
            var isNative = nativeEvents.has(typeEvent)

            var jQueryEvent
            var bubbles = true
            var nativeDispatch = true
            var defaultPrevented = false
            var evt = null

            if (inNamespace && $) {
                jQueryEvent = $.Event(event, args)

                $(element).trigger(jQueryEvent)
                bubbles = !jQueryEvent.isPropagationStopped()
                nativeDispatch = !jQueryEvent.isImmediatePropagationStopped()
                defaultPrevented = jQueryEvent.isDefaultPrevented()
            }

            if (isNative) {
                evt = document.createEvent('HTMLEvents')
                evt.initEvent(typeEvent, bubbles, true)
            } else {
                evt = new CustomEvent(event, {
                    bubbles,
                    cancelable: true
                })
            }

            // merge custom information in our event
            if (typeof args !== 'undefined') {
                Object.keys(args).forEach(function(key) {
                    Object.defineProperty(evt, key, {
                        get() {
                            return args[key]
                        }
                    })
                })
            }

            if (defaultPrevented) {
                evt.preventDefault()
            }

            if (nativeDispatch) {
                element.dispatchEvent(evt)
            }

            if (evt.defaultPrevented && typeof jQueryEvent !== 'undefined') {
                jQueryEvent.preventDefault()
            }

            return evt
        }
    }
});
