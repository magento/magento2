/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore',
    'uiRegistry',
    './dom-observer',
    'Magento_Ui/js/lib/ko/extender/bound-nodes',
    './bindings'
], function (ko, $, _, registry, domObserver, boundedNodes) {
    'use strict';

    /**
     * Checks if provided value is a dom element.
     *
     * @param {*} node - Value to be checked.
     * @returns {Boolean}
     */
    function isDomElement(node) {
        return typeof node === 'object' && node.tagName && node.nodeType;
    }

    /**
     * Parses provided string and extracts
     * component, context and selector data from it.
     *
     * @param {String} str - String to be processed.
     * @returns {Object} Data retrieved from string.
     *
     * @example Sample format.
     *      '{{component}}:{{ctx}} -> {{selector}}'
     *
     *      component - Name of component.
     *      ctx - Selector of the root node upon which component is binded.
     *      selector - Selector of DOM elements located
     *          inside of a previously specified context.
     */
    function parseSelector(str) {
        var data    = str.trim().split('->'),
            result  = {},
            componentData;

        if (data.length === 1) {
            if (!~data[0].indexOf(':')) {
                result.selector = data[0];
            } else {
                componentData = data[0];
            }
        } else {
            componentData   = data[0];
            result.selector = data[1];
        }

        if (componentData) {
            componentData = componentData.split(':');

            result.component = componentData[0];
            result.ctx = componentData[1];
        }

        _.each(result, function (value, key) {
            result[key] = value.trim();
        });

        return result;
    }

    /**
     * Internal method used to normalize argumnets passed
     * to 'async' module methods.
     *
     * @param {(String|Objetc)} selector
     * @param {(HTMLElement|Object|String)} [ctx]
     * @returns {Object}
     */
    function parseData(selector, ctx) {
        var data = {};

        if (arguments.length === 2) {
            data.selector = selector;

            if (isDomElement(ctx)) {
                data.ctx = ctx;
            } else {
                data.component = ctx;
                data.ctx = '*';
            }
        } else {
            data = _.isString(selector) ?
                parseSelector(selector) :
                selector;
        }

        return data;
    }

    /**
     * Creates promise that will be resolved
     * when requested component is registred.
     *
     * @param {String} name - Name of component.
     * @returns {jQueryPromise}
     */
    function waitComponent(name) {
        var deffer = $.Deferred();

        if (_.isString(name)) {
            registry.get(name, function (component) {
                deffer.resolve(component);
            });
        } else {
            deffer.resolve(name);
        }

        return deffer.promise();
    }

    /**
     * Creates listener for the nodes binded to provided component.
     *
     * @param {Object} data - Listener data.
     * @param {Object} component - Associated with nodes component.
     */
    function setRootListener(data, component) {
        boundedNodes.get(component, function (root) {
            if (!$(root).is(data.ctx || '*')) {
                return;
            }

            data.selector ?
                domObserver.get(data.selector, data.fn, root) :
                data.fn(root);
        });
    }

    /*eslint-disable no-unused-vars*/
    /**
     * Sets listener for the appearance of elements which
     * matches specified selector data.
     *
     * @param {(String|Object)} selector - Valid css selector or a string
     *      in format acceptable by 'parseSelector' method or an object with
     *      'component', 'selector' and 'ctx' properties.
     * @param {(HTMLElement|Object|String)} [ctx] - Optional context parameter
     *      which might be a DOM element, component instance or components' name.
     * @param {Function} fn - Callback that will be invoked
     *      when required DOM element appears.
     *
     * @example
     *      Creating listener of the 'span' nodes appearance,
     *      located inside of 'div' nodes, which are binded to 'cms_page_listing' component:
     *
     *      $.async('cms_page_listing:div -> span', function (node) {});
     *
     * @example Another syntaxes of the previous example.
     *      $.async({
     *          component: 'cms_page_listing',
     *          ctx: 'div',
     *          selector: 'span'
     *       }, function (node) {});
     *
     * @example Listens for appearance of any child node inside of specified component.
     *      $.async('> *', 'cms_page_lsiting', function (node) {});
     *
     * @example Listens for appearance of 'span' nodes inside of specific context.
     *      $.async('span', document.getElementById('test'), function (node) {});
     */
    $.async = function (selector, ctx, fn) {
        var args = _.toArray(arguments),
            data = parseData.apply(null, _.initial(args));

        data.fn = _.last(args);

        if (data.component) {
            waitComponent(data.component)
                .then(setRootListener.bind(null, data));
        } else {
            domObserver.get(data.selector, data.fn, data.ctx);
        }
    };

    /*eslint-enable no-unused-vars*/

    _.extend($.async, {

        /*eslint-disable no-unused-vars*/
        /**
         * Returns collection of elements found by provided selector data.
         *
         * @param {(String|Object)} selector - See 'async' definition.
         * @param {(HTMLElement|Object|String)} [ctx] - See 'async' definition.
         * @returns {Array} An array of DOM elements.
         */
        get: function (selector, ctx) {
            var data        = parseData.apply(null, arguments),
                component   = data.component,
                nodes;

            if (!component) {
                return $(data.selector, data.ctx).toArray();
            } else if (_.isString(component)) {
                component = registry.get(component);
            }

            if (!component) {
                return [];
            }

            nodes = boundedNodes.get(component);
            nodes = $(nodes).filter(data.ctx).toArray();

            return data.selector ?
                $(data.selector, nodes).toArray() :
                nodes;
        },

        /*eslint-enable no-unused-vars*/

        /**
         * Sets removal listener of the specified nodes.
         *
         * @param {(HTMLElement|Array|ArrayLike)} nodes - Nodes whose removal to track.
         * @param {Function} fn - Callback that will be invoked when node is removed.
         */
        remove: function (nodes, fn) {
            domObserver.remove(nodes, fn);
        },

        parseSelector: parseSelector
    });

    return $;
});
