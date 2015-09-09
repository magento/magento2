/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'MutationObserver'
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
     * Removes all non-element nodes from provided array
     * and appends to it descendant elements.
     *
     * @param {Array} nodes
     * @returns {Array}
     */
    function formNodesList(nodes) {
        var result = [],
            children;

        _.toArray(nodes)
            .filter(isElementNode)
            .forEach(function (node) {
                result.push(node);

                children = extractChildren(node);
                result   = result.concat(children);
            });

        return result;
    }

    /**
     * Invokes callback passing node to it.
     *
     * @param {HTMLElement} node
     * @param {Object} data
     */
    function trigger(node, data) {
        data.callback(node);
    }

    /**
     * Adds node to the observer list.
     *
     * @param {HTMLElement} node
     * @returns {Object}
     */
    function createNodeData(node) {
        var id      = node._observeId,
            nodes   = watchers.nodes;

        if (!id) {
            id = node._observeId = counter++;
        }

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

        if (typeof selector !== 'string') {
            return;
        }

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
     * Extarcts nodes that matches specfied selector.
     * If selector is an object, then it will be parsed as
     * one of the possible array like values.
     *
     * @param {(jQueryObject|HTMLElement|Array|String)} selector
     * @param {HTMLElement} [ctx=document.body] - Context that will be used to search for elements.
     * @returns {Array} An array of available elements.
     */
    function getNodes(selector, ctx) {
        var nodes = [];

        if (typeof selector === 'object') {
            if (typeof selector.jquery === 'string' || !selector.tagName) {
                nodes = _.toArray(selector);
            } else if (selector.tagName) {
                nodes = [selector];
            }
        } else if (typeof selector === 'string') {
            nodes = $(selector, ctx).toArray();
        }

        return nodes;
    }

    /**
     * Processes removed and added element nodes
     * specified in mutation record.
     *
     * @param {MutationRecord} mutation
     */
    function handleMutation(mutation) {
        var addedNodes = mutation.addedNodes,
            removedNodes = mutation.removedNodes;

        if (addedNodes.length) {
            formNodesList(addedNodes).forEach(processAdded);
        }

        if (removedNodes.length) {
            formNodesList(removedNodes).forEach(processRemoved);
        }
    }

    globalObserver = new MutationObserver(function (mutations) {
        mutations.forEach(handleMutation);
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
            var data;

            data = {
                ctx: ctx || document.body,
                callback: callback,
                type: 'add'
            };

            getNodes(selector, data.ctx).forEach(function (node) {
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
            var data;

            data = {
                ctx: ctx || document.body,
                callback: callback,
                type: 'remove'
            };

            getNodes(selector, data.ctx).forEach(function (node) {
                addRemovalListener(node, data);
            });

            if (typeof selector === 'string') {
                addSelectorListener(selector, data);
            }
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
