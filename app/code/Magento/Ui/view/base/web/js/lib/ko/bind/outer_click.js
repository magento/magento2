/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates outerClick binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'jquery',
    'underscore'
], function (ko, $, _) {
    'use strict';

    var defaults = {
        onlyIfVisible: true
    };

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
            if (!_.isNull(container.offsetParent)) {
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
                outerClick = onOuterClick.bind(null, element, config);

            $(document).on('click', outerClick);

            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                $(document).off('click', outerClick);
            });
        }
    };
});
