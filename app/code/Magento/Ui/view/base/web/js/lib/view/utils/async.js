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
     * Checks if provided  value is a dom element.
     *
     * @param {*} node - Value to be checked.
     * @returns {Boolean}
     */
    function isDomElement(node) {
        return typeof node === 'object' && node.tagName && node.nodeType;
    }

    /**
     *
     */
    function parseString(str) {
        var data = str.trim().split('->'),
            result = {};

        if (data.length === 1) {
            if (!~data[0].indexOf(':')) {
                result.selector = data[0];
            } else {
                data = data[0].split(':');

                result.component = data[0];
                result.ctx = data[1];
            }
        } else {
            result.selector = data[1];

            data = data[0].split(':');

            result.component = data[0];
            result.ctx = data[1];
        }

        _.each(result, function (value, key) {
            result[key] = value.trim();
        });

        return result;
    }

    /**
     *
     */
    function parseData(selector, ctx) {
        var data = {};

        if (arguments.length === 2) {
            data.selector = selector;

            if (isDomElement(ctx)) {
                data.ctx = ctx;
            } else {
                data.component = ctx;
            }
        } else {
            data = _.isString(selector) ?
                parseString(selector) :
                selector;
        }

        data.ctx = data.ctx || '*';

        return data;
    }

    /**
     * Creates promise that will be resolved
     * when requested component will be registred.
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
     *
     */
    function setRootListener(data, component) {
        boundedNodes.get(component, function (root) {
            if (!$(root).is(data.ctx)) {
                return;
            }

            data.selector ?
                domObserver.get(data.selector, data.fn, root) :
                data.fn(root);
        });
    }

    /**
     *
     */
    $.async = function () {
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

    _.extend($.async, {
        /**
         *
         * @returns {Array}
         */
        get: function () {
            var data        = parseData.apply(null, arguments),
                component   = data.component,
                nodes;

            if (data.component) {
                if (_.isString(component)) {
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
            }

            return $(data.selector, data.ctx).toArray();
        },

        /**
         *
         */
        remove: function (selector, ctx, fn) {
            var args = _.toArray(arguments);

            fn = _.last(args);

            if (!_.isString(selector)) {
                domObserver.remove(selector, fn);
            }
        },

        parseString: parseString
    });

    return $;
});
