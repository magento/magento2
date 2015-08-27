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
     *
     */
    function isDomElement(node) {
        return node.tagName && node.nodeType;
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
                data.ctx = '*';
            }
        } else {
            data = _.isString(selector) ?
                parseString(selector) :
                selector;
        }

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
