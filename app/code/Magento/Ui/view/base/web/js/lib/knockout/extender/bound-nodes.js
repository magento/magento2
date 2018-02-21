/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mage/utils/wrapper',
    'uiEvents',
    'es6-collections'
], function (ko, _, wrapper, Events) {
    'use strict';

    var nodesMap = new WeakMap();

    /**
     * Returns a array of nodes associated with a specified model.
     *
     * @param {Object} model
     * @returns {Undefined|Array}
     */
    function getBounded(model) {
        return nodesMap.get(model);
    }

    /**
     * Removes specified node to models' associations list, if it's
     * a root node (node is not a descendant of any previously added nodes).
     * Triggers 'addNode' event.
     *
     * @param {Object} model
     * @param {HTMLElement} node
     */
    function addBounded(model, node) {
        var nodes = getBounded(model),
            isRoot;

        if (!nodes) {
            nodesMap.set(model, [node]);

            Events.trigger.call(model, 'addNode', node);

            return;
        }

        isRoot = nodes.every(function (bounded) {
            return !bounded.contains(node);
        });

        if (isRoot) {
            nodes.push(node);

            Events.trigger.call(model, 'addNode', node);
        }
    }

    /**
     * Removes specified node from models' associations list.
     * Triggers 'removeNode' event.
     *
     * @param {Object} model
     * @param {HTMLElement} node
     */
    function removeBounded(model, node) {
        var nodes = getBounded(model),
            index;

        if (!nodes) {
            return;
        }

        index = nodes.indexOf(node);

        if (~index) {
            nodes.splice(index, 0);

            Events.trigger.call(model, 'removeNode', node);
        }

        if (!nodes.length) {
            nodesMap.delete(model);
        }
    }

    /**
     * Returns node's first sibling of 'element' type within the common component scope
     *
     * @param {HTMLElement} node
     * @returns {HTMLElement}
     */
    function getElement(node, data) {
        var elem;

        while (node.nextElementSibling) {
            node = node.nextElementSibling;

            if (node.nodeType === 1 && ko.dataFor(node) === data) {
                elem = node;
                break;
            }
        }

        return elem;
    }

    wrapper.extend(ko, {

        /**
         * Extends kncokouts' 'applyBindings'
         * to track nodes associated with model.
         *
         * @param {Function} orig - Original 'applyBindings' method.
         */
        applyBindings: function (orig, ctx, node) {
            var result = orig(),
                data = ctx && (ctx.$data || ctx);

            if (node && node.nodeType === 8) {
                node = getElement(node, data);
            }

            if (!node || node.nodeType !== 1) {
                return result;
            }

            if (data && data.registerNodes) {
                addBounded(data, node);
            }

            return result;
        },

        /**
         * Extends kncokouts' cleanNode
         * to track nodes associated with model.
         *
         * @param {Function} orig - Original 'cleanNode' method.
         */
        cleanNode: function (orig, node) {
            var result = orig(),
                data;

            if (node.nodeType !== 1) {
                return result;
            }

            data = ko.dataFor(node);

            if (data && data.registerNodes) {
                removeBounded(data, node);
            }

            return result;
        }
    });

    return {

        /**
         * Returns root nodes associated with a model. If callback is provided,
         * will iterate through all of the present nodes triggering callback
         * for each of it. Also it will subscribe to the 'addNode' event.
         *
         * @param {Object} model
         * @param {Function} [callback]
         * @returns {Array|Undefined}
         */
        get: function (model, callback) {
            var nodes = getBounded(model) || [];

            if (!_.isFunction(callback)) {
                return nodes;
            }

            nodes.forEach(function (node) {
                callback(node);
            });

            this.add.apply(this, arguments);
        },

        /**
         * Subscribes to adding of nodes associated with a model.
         *
         * @param {Object} model
         */
        add: function (model) {
            var args = _.toArray(arguments).slice(1);

            args.unshift('addNode');

            Events.on.apply(model, args);
        },

        /**
         * Subscribes to removal of nodes associated with a model.
         *
         * @param {Object} model
         */
        remove: function (model) {
            var args = _.toArray(arguments).slice(1);

            args.unshift('removeNode');

            Events.on.apply(model, args);
        },

        /**
         * Removes subscriptions from the model.
         *
         * @param {Object} model
         */
        off: function (model) {
            var args = _.toArray(arguments).slice(1);

            Events.off.apply(model, args);
        }
    };
});
