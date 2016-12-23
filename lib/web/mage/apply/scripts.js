/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    var scriptSelector = 'script[type="text/x-magento-init"]',
        dataAttr = 'data-mage-init',
        virtuals = [];

    /**
     * Adds components to the virtula list.
     *
     * @param {Object} components
     */
    function addVirtual(components) {
        virtuals.push({
            el: false,
            data: components
        });
    }

    /**
     * Merges provided data with a current data
     * of a elements' "data-mage-init" attribute.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {HTMLElement} elem - Element whose data should be modified.
     */
    function setData(components, elem) {
        var data = elem.getAttribute(dataAttr);

        data = data ? JSON.parse(data) : {};
        _.each(components, function (obj, key) {
            if (_.has(obj, 'mixins')) {
                data[key] = data[key] || {};
                data[key].mixins = data[key].mixins || [];
                data[key].mixins = data[key].mixins.concat(obj.mixins);
                delete obj.mixins;
            }
        });

        data = $.extend(true, data, components);
        data = JSON.stringify(data);
        elem.setAttribute(dataAttr, data);
    }

    /**
     * Search for the elements by privded selector and extends theirs data.
     *
     * @param {Object} components - Object with components and theirs configuration.
     * @param {String} selector - Selector for the elements.
     */
    function processElems(components, selector) {
        var elems,
            iterator;

        if (selector === '*') {
            addVirtual(components);

            return;
        }

        elems = document.querySelectorAll(selector);
        iterator = setData.bind(null, components);

        _.toArray(elems).forEach(iterator);
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

    /**
     * Parses 'script' tags with a custom type attribute and moves it's data
     * to a 'data-mage-init' attribute of an element found by provided selector.
     * Note: All found script nodes will be removed from DOM.
     *
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
    return function () {
        var nodes = document.querySelectorAll(scriptSelector);

        _.toArray(nodes)
            .map(getNodeData)
            .forEach(function (item) {
                _.each(item, processElems);
            });

        return virtuals.splice(0, virtuals.length);
    };
});
