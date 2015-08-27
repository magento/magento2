define([
    'underscore',
    'jquery',
    'ko'
], function (_, $, ko) {
    'use strict';

    /**
     *
     */
    function isDomElement(node) {
        return node && node.tagName && node.nodeType;
    }

    /**
     *
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
     */
    $.fn.extendCtx = function () {
        var nodes = normalize(_.toArray(this)),
            extenders = _.toArray(arguments);

        nodes.forEach(function (node) {
            var ctx  = ko.contextFor(node),
                data = [ctx].concat(extenders);

            _.extend.apply(_, data);
        });

        return this;
    };

    /**
     *
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

            if (typeof data === 'function') {
                bindings = data(nodeCtx, node);
            }

            ko.applyBindingsToNode(node, bindings, nodeCtx);
        });

        return this;
    };
});
