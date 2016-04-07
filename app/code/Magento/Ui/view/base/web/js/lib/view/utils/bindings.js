/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore'
], function (ko, $, _) {
    'use strict';

    /**
     * Checks if provided  value is a dom element.
     *
     * @param {*} node - Value to be checked.
     * @returns {Boolean}
     */
    function isDomElement(node) {
        return typeof node === 'object' && node.tagName && node.nodeType;
    }

    /**
     * Removes from the provided array all non-root nodes located inside
     * of the comment element as long as the closing comment tags.
     *
     * @param {(Array|ArrayLike)} nodes - An array of nodes to be processed.
     * @returns {Array}
     */
    function normalize(nodes) {
        var result;

        nodes   = _.toArray(nodes);
        result  = nodes.slice();

        nodes.forEach(function (node) {
            if (node.nodeType === 8) {
                result = !ko.virtualElements.hasBindingValue(node) ?
                    _.without(result, node) :
                    _.difference(result, ko.virtualElements.childNodes(node));
            }
        });

        return result;
    }

    /**
     * Extends binding context of each item in the collection.
     *
     * @param {...Object} extenders - Multiple extender objects to be applied to the context.
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.extendCtx = function () {
        var nodes       = normalize(this),
            extenders   = _.toArray(arguments);

        nodes.forEach(function (node) {
            var ctx  = ko.contextFor(node),
                data = [ctx].concat(extenders);

            _.extend.apply(_, data);
        });

        return this;
    };

    /**
     * Evaluates bindings specified in each DOM element of collection.
     *
     * @param {(HTMLElement|Object)} [ctx] - Context to use for bindings evaluation.
     *      If not specified then current context of a collections' item will be used.
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.applyBindings = function (ctx) {
        var nodes = normalize(this),
            nodeCtx;

        if (isDomElement(ctx)) {
            ctx = ko.contextFor(ctx);
        }

        nodes.forEach(function (node) {
            nodeCtx = ctx || ko.contextFor(node);

            ko.applyBindings(nodeCtx, node);
        });

        return this;
    };

    /**
     * Adds specfied bindings to each DOM elemenet in
     * collection and evalutes them with provided context.
     *
     * @param {(Object|Function)} data - Either bindings object or a function
     *      which returns bindings data for each element in collection.
     * @param {(HTMLElement|Object)} [ctx] - Context to use for bindings evaluation.
     *      If not specified then current context of a collections' item will be used.
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.bindings = function (data, ctx) {
        var nodes    = normalize(this),
            bindings = data,
            nodeCtx;

        if (isDomElement(ctx)) {
            ctx = ko.contextFor(ctx);
        }

        nodes.forEach(function (node) {
            nodeCtx = ctx || ko.contextFor(node);

            if (_.isFunction(data)) {
                bindings = data(nodeCtx, node);
            }

            ko.applyBindingsToNode(node, bindings, nodeCtx);
        });

        return this;
    };
});
