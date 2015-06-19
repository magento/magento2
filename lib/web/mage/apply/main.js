/**
 * Copyright © 2015 Magento. All rights reserved.
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

    /**
     * Initializes components assigned to a specfied element via data-* attribute.
     *
     * @param {HTMLElement} el - Element to initialize components with.
     * @param {Object|String} config - Initial components' config.
     * @param {String} component - Components' path.
     */
    function init(el, config, component) {
        require([component], function (fn) {
            if (typeof fn === 'object') {
                fn = fn[component];
            }

            if (_.isFunction(fn)) {
                fn(config, el);
            } else if ($(el)[component]) {
                $(el)[component](config);
            }
        });
    }
    /**
     * Initializes mixins assigned to a specfied component.
     *
     * @param {Object} mixins - object contains mixins list.
     * @param {Object} itemContainer - object with component config and DOM element link.
     */
    function initMixins(mixins, itemContainer){
        var configStack = {},
            element = itemContainer.el;

        _.each(mixins, function (mixin, componentName) {
            configStack = itemContainer.data[componentName];
            _.each(mixin, function (item) {
                require([item], function (fn) {
                    configStack = fn(configStack, element);
                });
            });
            itemContainer.data[componentName] = configStack;
        });
        delete itemContainer.data.mixins;

        return itemContainer;
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

    return {
        /**
         * Initializes components assigned to HTML elements via [data-mage-init].
         *
         * @example Sample 'data-mage-init' declaration.
         *      data-mage-init='{"path/to/component": {"foo": "bar"}}'
         */
        apply: function () {
            var virtuals = processScripts(),
                nodes = document.querySelectorAll(nodeSelector);

            _.toArray(nodes)
                .map(getData)
                .concat(virtuals)
                .forEach(function (itemContainer) {
                    var mixins = itemContainer.data.mixins;

                    if(mixins) {
                        $.when(itemContainer = initMixins(mixins, itemContainer)).then(_.each(itemContainer.data, init.bind(null, itemContainer.el)));
                    } else {
                        _.each(itemContainer.data, init.bind(null, itemContainer.el));
                    }
                });
        },
        applyFor: init
    };
});
