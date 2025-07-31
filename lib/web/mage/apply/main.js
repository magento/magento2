/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    './scripts'
], function (_, $, processScripts) {
    'use strict';

    var dataAttr = 'data-mage-init',
        nodeSelector = '[' + dataAttr + ']';

    const idleCallback = window.requestIdleCallback ?? setTimeout;

    /**
     * Initializes components assigned to a specified element via data-* attribute.
     *
     * @param {HTMLElement} el - Element to initialize components with.
     * @param {Object|String} config - Initial components' config.
     * @param {String} component - Components' path.
     */
    function init(el, config, component) {
        require([component], function (fn) {
            var $el;

            if (typeof fn === 'object') {
                fn = fn[component].bind(fn);
            }

            if (_.isFunction(fn)) {
                fn = fn.bind(null, config, el);
            } else {
                $el = $(el);

                if ($el[component]) {
                    // eslint-disable-next-line jquery-no-bind-unbind
                    fn = $el[component].bind($el, config);
                }
            }
            // Init module in separate task to prevent blocking main thread.
            if (fn) {
                idleCallback(fn);
            }
        }, function (error) {
            if ('console' in window && typeof window.console.error === 'function') {
                console.error(error);
            }

            return true;
        });
    }

    /**
     * Initializes components assigned to a specified element via mage.applyFor
     *
     * @param {HTMLElement} el
     * @param {Object|String} config
     * @param {String} component
     */
    function applyFor(el, config, component) {
        if (!el || isInViewport(el)) {
            init(el, config, component)
        } else {
            (events => {
                const lazyLoadJs = () => {
                    events.forEach(type => window.removeEventListener(type, lazyLoadJs))
                    init(el, config, component);
                };

                events.forEach(type => window.addEventListener(type, lazyLoadJs, {once: true}))
            })(['touchstart', 'mouseover', 'wheel', 'scroll', 'keydown']);
        }
    }

    /**
     * Check if an element is visible in the viewport
     *
     * @param {HTMLElement} el
     * @returns bool
     */
    function isInViewport(el) {
        if ((!_.isFunction(el.checkVisibility)) || !el.checkVisibility()) {
            return false;
        }

        const rect = el.getBoundingClientRect(),
            vWidth = window.innerWidth || doc.documentElement.clientWidth,
            vHeight = window.innerHeight || doc.documentElement.clientHeight;

        // Check if the element is out of bounds
        if (rect.right < 0 || rect.bottom < 0 || rect.left > vWidth || rect.top > vHeight) {
            return false;
        }

        // Return true if any of the above disjunctions are false
        return true;
    }

    /**
     * Parses elements 'data-mage-init' attribute as a valid JSON data.
     * Note: data-mage-init attribute will be removed.
     *
     * @param {HTMLElement} el - Element whose attribute should be parsed.
     * @returns {Object}
     */
    function getData(el) {
        var data = el.getAttribute(dataAttr);

        el.removeAttribute(dataAttr);

        return {
            el: el,
            data: JSON.parse(data)
        };
    }

    /**
     * Process component
     *
     * @param {HTMLElement} element
     * @param {Object} itemContainer
     */
    function process(element, itemContainer) {
        _.each(itemContainer.data, function (obj, key) {
            if (obj.mixins) {
                require(obj.mixins, function () { //eslint-disable-line max-nested-callbacks
                    var i, len;

                    for (i = 0, len = arguments.length; i < len; i++) {
                        $.extend(
                            true,
                            itemContainer.data[key],
                            arguments[i](itemContainer.data[key], element)
                        );
                    }

                    delete obj.mixins;
                    init.call(null, element, obj, key);
                });
            } else {
                init.call(null, element, obj, key);
            }
        });
    }

    /**
     * Lazy process component via event
     *
     * @param {HTMLElement} element
     * @param {Object} itemContainer
     */
    function lazyProcess(element, itemContainer) {
        (events => {
            const lazyLoadJs = () => {
                events.forEach(type => window.removeEventListener(type, lazyLoadJs))
                process(element, itemContainer);
            };

            events.forEach(type => window.addEventListener(type, lazyLoadJs, {once: true}))
        })(['touchstart', 'mouseover', 'wheel', 'scroll', 'keydown']);
    }

    return {
        /**
         * Initializes components assigned to HTML elements via [data-mage-init].
         *
         * @example Sample 'data-mage-init' declaration.
         *      data-mage-init='{"path/to/component": {"foo": "bar"}}'
         */
        apply: function (context) {
            var virtuals = processScripts(!context ? document : context),
                nodes = document.querySelectorAll(nodeSelector);

            _.toArray(nodes)
                .map(getData)
                .concat(virtuals)
                .forEach(function (itemContainer) {
                    var element = itemContainer.el;

                    if (!element) {
                        idleCallback(function () {
                            process(element, itemContainer);
                        });
                    } else {
                        if (isInViewport(element)) {
                            process(element, itemContainer);
                        } else {
                            lazyProcess(element, itemContainer);
                        }
                    };
                });
        },
        applyFor: applyFor
    };
});
