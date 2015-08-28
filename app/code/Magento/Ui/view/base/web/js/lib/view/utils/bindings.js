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
     * @param {Array} nodes - An array of nodes to be processed.
     * @returns {Array}
     */
    function normalize(nodes) {
        var result = nodes.slice();

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
     *
     * @param {...Object} extenders
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.extendCtx = function () {
        var nodes       = normalize(_.toArray(this)),
            extenders   = _.toArray(arguments);

        nodes.forEach(function (node) {
            var ctx  = ko.contextFor(node),
                data = [ctx].concat(extenders);

            _.extend.apply(_, data);
        });

        return this;
    };

    /**
     *
     *
     * @param {(HTMLElement|Object)} [ctx]
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.applyBindings = function (ctx) {
        var nodes = normalize(_.toArray(this)),
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
     *
     *
     * @param {(Object|Function)} data
     * @param {(HTMLElement|Object)} [ctx]
     * @returns {jQueryCollection} Chainable.
     */
    $.fn.bindings = function (data, ctx) {
        var nodes    = normalize(_.toArray(this)),
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
