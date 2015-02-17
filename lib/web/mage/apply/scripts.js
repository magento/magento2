/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    var scriptSelector = 'script[type="text/x-magento-init"]',
        virtual = [];

    /**
     * Adds components to the virtula list.
     *
     * @param {Object} components
     */
    function addVirtual(components) {
        virtual.push({
            el: false,
            data: components
        });
    }

    /**
     * Merges provided data with a current data
     * of a elements' "data-mage-init" attribute.
     *
     * @param {Object} components - Object with compoenets and theirs configuration.
     * @param {HTMLElement} elem - Element whose data should be modified.
     */
    function setData(components, elem) {
        var data = elem.getAttribute('data-mage-init');

        data = !!data ? JSON.parse(data) : {};
        data = $.extend(true, data, components);
        data = JSON.stringify(data);

        elem.setAttribute('data-mage-init', data);
    }

    /**
     * Search for the elements by privded selector and extends theirs data.
     *
     * @param {Object} components - Object with compoenets and theirs configuration.
     * @param {String} selector - Selector for the elements.
     */
    function processElems(components, selector) {
        var setFn,
            elems;

        if (selector === '*') {
            addVirtual(components);
        } else {
            elems = document.querySelectorAll(selector);
            setFn = setData.bind(null, components);

            _.toArray(elems).forEach(setFn);
        }
    }

    /**
     * Searches for script tags whith a 'text/x-magento-init' type.
     *
     * @param {HTMLElement} [parent=document.body] - Optional node inside of which to perform search.
     * @returns {Array} An array of elements script nodes.
     */
    function getNodes(parent) {
        var elems;

        parent = parent || document.body;

        elems = document.querySelectorAll(scriptSelector);

        return _.toArray(elems);
    }

    /**
     * Parses content of a provided script node.
     * Note: node will be removed from DOM.
     *
     * @param {HTMLScriptElement} node - Node to be processed.
     * @returns {Object}
     */
    function getNodeData(node) {
        var data = node.textContent;

        node.parentNode.removeChild(node);

        return JSON.parse(data);
    }

    return {
        /**
         * Parses 'script' tags with a custom type attribute and moves it's data
         * to a 'data-mage-init' attribute of an elemennt found by provided selector.
         * Note: All found script nodes would be removed from DOM.
         *
         * @param {HTMLElement} [ctx=document.body] - Optional node to search inside.
         * @returns {Array} An array of components not assigned to the specific element.
         *
         * @example Sample declaration.
         *      <script type="text/x-magento-init">
         *          {
         *              "body": {
         *                  "path/to/component": {"foo": "bar"}
         *              }
         *          }
         *      </script>
         *
         * @example Providing data without selector.
         *      {
         *          "*": {
         *              "path/to/component": {"bar": "baz"}
         *          }
         *      }
         */
        process: function (ctx) {
            getNodes(ctx)
                .map(getNodeData)
                .forEach(function (item) {
                    _.each(item, processElems);
                });

            return virtual.splice(0, virtual.length);
        }
    };
});
