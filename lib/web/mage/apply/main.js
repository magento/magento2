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

    var dataAttr = 'data-mage-init';

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
     * Searches for elements with a 'data-mage-init' attribute.
     *
     * @returns {Array} An array of elements with 'data-mage-init' attribute.
     */
    function getElems() {
        var body = document.body,
            elems;

        elems = body.querySelectorAll('[' + dataAttr + ']');
        elems = _.toArray(elems);

        if (body.hasAttribute(dataAttr)) {
            elems.unshift(body);
        }

        return elems;
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
            var virtual = processScripts();

            getElems()
                .map(getData)
                .concat(virtual)
                .forEach(function (item) {
                    _.each(item.data, init.bind(null, item.el));
                });
        },

        applyFor: init
    };
});
