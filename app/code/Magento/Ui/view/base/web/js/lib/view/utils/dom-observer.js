/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'MutationObserver',
    'domReady!'
], function ($, _) {
    'use strict';

    var counter = 1,
        watchers,
        globalObserver;

    watchers = {
        selectors: {},
        nodes: {}
    };

    /**
     * Checks if node represents an element node (nodeType === 1).
     *
     * @param {HTMLElement} node
     * @returns {Boolean}
     */
    function isElementNode(node) {
        return node.nodeType === 1;
    }

    /**
     * Extracts all child descendant
     * elements of a specified node.
     *
     * @param {HTMLElement} node
     * @returns {Array}
     */
    function extractChildren(node) {
        var children = node.querySelectorAll('*');

        return _.toArray(children);
    }

    /**
     * Extracts node identifier. If ID is not specified,
     * then it will be created for the provided node.
     *
     * @param {HTMLElement} node
     * @returns {Number}
     */
    function getNodeId(node) {
        var id = node._observeId;

        if (!id) {
            id = node._observeId = counter++;
        }

        return id;
    }

    /**
     * Invokes callback passing node to it.
     *
     * @param {HTMLElement} node
     * @param {Object} data
     */
    function trigger(node, data) {
        var id = getNodeId(node),
            ids = data.invoked;

        if (_.contains(ids, id)) {
            return;
        }

        data.callback(node);
        data.invoked.push(id);
    }

    /**
     * Adds node to the observer list.
     *
     * @param {HTMLElement} node
     * @returns {Object}
     */
    function createNodeData(node) {
        var nodes   = watchers.nodes,
            id      = getNodeId(node);

        nodes[id] = nodes[id] || {};

        return nodes[id];
    }

    /**
     * Returns data associated with a specified node.
     *
     * @param {HTMLElement} node
     * @returns {Object|Undefined}
     */
    function getNodeData(node) {
        var nodeId = node._observeId;

        return watchers.nodes[nodeId];
    }

    /**
     * Removes data associated with a specified node.
     *
     * @param {HTMLElement} node
     */
    function removeNodeData(node) {
        var nodeId = node._observeId;

        delete watchers.nodes[nodeId];
    }

    /**
     * Adds removal listener for a specified node.
     *
     * @param {HTMLElement} node
     * @param {Object} data
     */
    function addRemovalListener(node, data) {
        var nodeData = createNodeData(node);

        (nodeData.remove = nodeData.remove || []).push(data);
    }

    /**
     * Adds listener for the nodes which matches specified selector.
     *
     * @param {String} selector - CSS selector.
     * @param {Object} data
     */
    function addSelectorListener(selector, data) {
        var storage = watchers.selectors;

        (storage[selector] = storage[selector] || []).push(data);
    }

    /**
     * Calls handlers assocoiated with an added node.
     * Adds listeners for the node removal.
     *
     * @param {HTMLElement} node - Added node.
     */
    function processAdded(node) {
        _.each(watchers.selectors, function (listeners, selector) {
            listeners.forEach(function (data) {
                if (!data.ctx.contains(node) || !$(node, data.ctx).is(selector)) {
                    return;
                }

                if (data.type === 'add') {
                    trigger(node, data);
                } else if (data.type === 'remove') {
                    addRemovalListener(node, data);
                }
            });
        });
    }

    /**
     * Calls handlers assocoiated with a removed node.
     *
     * @param {HTMLElement} node - Removed node.
     */
    function processRemoved(node) {
        var nodeData    = getNodeData(node),
            listeners   = nodeData && nodeData.remove;

        if (!listeners) {
            return;
        }

        listeners.forEach(function (data) {
            trigger(node, data);
        });

        removeNodeData(node);
    }

    /**
     * Removes all non-element nodes from provided array
     * and appends to it descendant elements.
     *
     * @param {Array} nodes
     * @returns {Array}
     */
    function formNodesList(nodes) {
        var result = [],
            children;

        nodes = _.toArray(nodes).filter(isElementNode);

        nodes.forEach(function (node) {
            result.push(node);

            children = extractChildren(node);
            result   = result.concat(children);
        });

        return result;
    }

    /**
     * Collects all removed and added nodes from
     * mutation records into separate arrays
     * while removing duplicates between both types of changes.
     *
     * @param {Array} mutations - An array of mutation records.
     * @returns {Object} Object with 'removed' and 'added' nodes arrays.
     */
    function formChangesLists(mutations) {
        var removed = [],
            added = [];

        mutations.forEach(function (record) {
            removed = removed.concat(_.toArray(record.removedNodes));
            added   = added.concat(_.toArray(record.addedNodes));
        });

        removed = removed.filter(function (node) {
            var addIndex = added.indexOf(node),
                wasAdded = !!~addIndex;

            if (wasAdded) {
                added.splice(addIndex, 1);
            }

            return !wasAdded;
        });

        return {
            removed: formNodesList(removed),
            added: formNodesList(added)
        };
    }

    globalObserver = new MutationObserver(function (mutations) {
        var changes = formChangesLists(mutations);

        changes.removed.forEach(processRemoved);
        changes.added.forEach(processAdded);
    });

    globalObserver.observe(document.body, {
        subtree: true,
        childList: true
    });

    return {

        /**
         * Adds listener for the appearance of nodes that matches provided
         * selector and which are inside of the provided context. Callback will be
         * also invoked on elements which a currently present.
         *
         * @param {String} selector - CSS selector.
         * @param {Function} callback - Function that will invoked when node appears.
         * @param {HTMLElement} [ctx=document.body] - Context inside of which to search for the node.
         */
        get: function (selector, callback, ctx) {
            var data,
                nodes;

            data = {
                ctx: ctx || document.body,
                type: 'add',
                callback: callback,
                invoked: []
            };

            nodes = $(selector, data.ctx).toArray();

            nodes.forEach(function (node) {
                trigger(node, data);
            });

            addSelectorListener(selector, data);
        },

        /**
         * Adds listener for the nodes removal.
         *
         * @param {(jQueryObject|HTMLElement|Array|String)} selector
         * @param {Function} callback - Function that will invoked when node is removed.
         * @param {HTMLElement} [ctx=document.body] - Context inside of which to search for the node.
         */
        remove: function (selector, callback, ctx) {
            var nodes = [],
                data;

            data = {
                ctx: ctx || document.body,
                type: 'remove',
                callback: callback,
                invoked: []
            };

            if (typeof selector === 'object') {
                nodes = !_.isUndefined(selector.length) ?
                    _.toArray(selector) :
                    [selector];
            } else if (_.isString(selector)) {
                nodes = $(selector, ctx).toArray();

                addSelectorListener(selector, data);
            }

            nodes.forEach(function (node) {
                addRemovalListener(node, data);
            });
        },

        /**
         * Removes listeners.
         *
         * @param {String} selector
         * @param {Function} [fn]
         */
        off: function (selector, fn) {
            var selectors = watchers.selectors,
                listeners = selectors[selector];

            if (selector && !fn) {
                delete selectors[selector];
            } else if (listeners && fn) {
                selectors[selector] = listeners.filter(function (data) {
                    return data.callback !== fn;
                });
            }
        }
    };
});
