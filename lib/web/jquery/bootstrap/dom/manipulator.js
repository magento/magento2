/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.1.3): dom/manipulator.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

define([], function() {
    'use strict';

    function normalizeData(val) {
        if (val === 'true') {
            return true
        }

        if (val === 'false') {
            return false
        }

        if (val === Number(val).toString()) {
            return Number(val)
        }

        if (val === '' || val === 'null') {
            return null
        }

        return val
    }

    function normalizeDataKey(key) {
        return key.replace(/[A-Z]/g, chr => `-${chr.toLowerCase()}`)
    }

    return {
        setDataAttribute: function(element, key, value) {
            element.setAttribute(`data-bs-${normalizeDataKey(key)}`, value)
        },

        removeDataAttribute: function(element, key) {
            element.removeAttribute(`data-bs-${normalizeDataKey(key)}`)
        },

        getDataAttributes: function(element) {
            if (!element) {
                return {}
            }

            const attributes = {}

            Object.keys(element.dataset)
                .filter(key => key.startsWith('bs'))
                .forEach(key => {
                    let pureKey = key.replace(/^bs/, '')
                    pureKey = pureKey.charAt(0).toLowerCase() + pureKey.slice(1, pureKey.length)
                    attributes[pureKey] = normalizeData(element.dataset[key])
                })

            return attributes
        },

        getDataAttribute: function(element, key) {
            return normalizeData(element.getAttribute(`data-bs-${normalizeDataKey(key)}`))
        },

        offset: function(element) {
            const rect = element.getBoundingClientRect()

            return {
                top: rect.top + window.pageYOffset,
                left: rect.left + window.pageXOffset
            }
        },

        position: function(element) {
            return {
                top: element.offsetTop,
                left: element.offsetLeft
            }
        }
    }
});
