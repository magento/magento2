/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates outerClick binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'jquery',
    'underscore',
    '../template/renderer'
], function (ko, $, _, renderer) {
    'use strict';

    var defaults = {
        onlyIfVisible: true
    };

    /**
     * Checks if element sis visible.
     *
     * @param {Element} el
     * @returns {Boolean}
     */
    function isVisible(el) {
        var style = window.getComputedStyle(el),
            visibility = {
                display: 'none',
                visibility: 'hidden',
                opacity: '0'
            },
            visible = true;

        _.each(visibility, function (val, key) {
            if (style[key] === val) {
                visible = false;
            }
        });

        return visible;
    }

    /**
     * Document click handler which in case if event target is not
     * a descendant of provided container element,
     * invokes specfied in configuration callback.
     *
     * @param {HTMLElement} container
     * @param {Object} config
     * @param {EventObject} e
     */
    function onOuterClick(container, config, e) {
        var target = e.target,
            callback = config.callback;

        if (container === target || container.contains(target)) {
            return;
        }

        if (config.onlyIfVisible) {
            if (!_.isNull(container.offsetParent) && isVisible(container)) {
                callback();
            }
        } else {
            callback();
        }
    }

    /**
     * Prepares configuration for the binding based
     * on a default properties and provided options.
     *
     * @param {(Object|Function)} [options={}]
     * @returns {Object}
     */
    function buildConfig(options) {
        var config = {};

        if (_.isFunction(options)) {
            options = {
                callback: options
            };
        } else if (!_.isObject(options)) {
            options = {};
        }

        return _.extend(config, defaults, options);
    }

    ko.bindingHandlers.outerClick = {

        /**
         * Initializes outer click binding.
         */
        init: function (element, valueAccessor) {
            var config = buildConfig(valueAccessor()),
                outerClick = onOuterClick.bind(null, element, config),
                isTouchDevice = typeof document.ontouchstart !== 'undefined';

            if (isTouchDevice) {
                $(document).on('touchstart', outerClick);

                ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                    $(document).off('touchstart', outerClick);
                });
            } else {
                $(document).on('click', outerClick);

                ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                    $(document).off('click', outerClick);
                });
            }
        }
    };

    renderer.addAttribute('outerClick');
});
