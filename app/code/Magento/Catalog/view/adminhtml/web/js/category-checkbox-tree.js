/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'jquery/jstree/jquery.jstree',
    'mage/adminhtml/form'
], function ($) {
    'use strict';

    /**
     * Recursively adds the 'lastNode' property to the nodes in the nested object.
     *
     * @param {Array} nodes
     * @returns {Array}
     */
    function addLastNodeProperty(nodes) {
        return nodes.map(node => {
            return node.children ? {
                ...node,
                children: addLastNodeProperty(node.children)
            } : {
                ...node,
                lastNode: true
            };
        });
    }

    /**
     * Main function that creates the jstree
     *
     * @param {Object} config - Configuration object containing various options
     */
    return function (config) {

        let options = {
                dataUrl: config.dataUrl,
                divId: config.divId,
                rootVisible: config.rootVisible,
                useAjax: config.useAjax,
                currentNodeId: config.currentNodeId,
                jsFormObject: window[config.jsFormObject],
                name: config.name,
                checked: config.checked,
                allowDrop: config.allowDrop,
                rootId: config.rootId,
                expanded: config.expanded,
                categoryId: config.categoryId,
                treeJson: addLastNodeProperty(config.treeJson)
            },
            checkedNodes = [];

        /**
         * Get the jstree element by its ID
         */
        const treeId = $('#' + options.divId);

        /**
         * Function to check child nodes based on the checkedNodes array
         *
         * @param {Object} node
         */
        function getCheckedNodeIds(node) {
            if (node.children_d && node.children_d.length > 0) {
                const selectChildrenNodes = node.children_d.filter(item => checkedNodes.includes(item));

                if (selectChildrenNodes.length > 0) {
                    treeId.jstree(false).select_node(selectChildrenNodes);
                }
            }
        }

        /**
         * Initialize the jstree with configuration options
         */
        treeId.jstree({
            core: {
                data: options.treeJson,
                check_callback: true
            },
            plugins: ['checkbox'],
            checkbox: {
                three_state: false
            }
        });

        /**
         * Event handler for 'loaded.jstree' event
         */
        treeId.on('loaded.jstree', function () {

            /**
             * Get each node in the tree
             */
            $(treeId.jstree().get_json('#', {
                flat: false
            })).each(function () {
                let node = treeId.jstree().get_node(this.id, false);

                if (node.original.expanded) {
                    treeId.jstree(true).open_node(node);
                }

                if (options.jsFormObject.updateElement.defaultValue) {
                    checkedNodes = options.jsFormObject.updateElement.defaultValue.split(',');
                }
            });
        });

        /**
         * Event handler for 'load_node.jstree' event
         */
        treeId.on('load_node.jstree', function (e, data) {
            getCheckedNodeIds(data.node);
        });

        /**
         * Add lastNode property to child who doesn't have children property
         *
         * @param {Object} treeData
         */
        function addLastNodeFlag(treeData) {
            if (treeData.children) {
                treeData.children.forEach((child) => addLastNodeFlag(child));
            } else {
                treeData.lastNode = true;
            }
        }

        /**
         * Function to handle the 'success' callback of the AJAX request
         *
         * @param {Array} response
         * @param {Object} childNode
         * @param {Object} data
         */
        function handleSuccessResponse(response, childNode, data) {
            if (response.length > 0) {
                response.forEach(function (newNode) {
                    addLastNodeFlag(newNode);

                    /**
                     * Create the new node and execute node callback
                     */
                    data.instance.create_node(childNode, newNode, 'last', function (node) {
                        if (checkedNodes.includes(node.id)) {
                            treeId.jstree(false).select_node(node.id);
                        }
                        getCheckedNodeIds(node);
                    });
                });
            }
        }

        /**
         * Event handler for 'open_node.jstree' event
         */
        treeId.on('open_node.jstree', function (e, data) {
            let parentNode = data.node;

            if (parentNode.children.length > 0) {
                let childNode = data.instance.get_node(parentNode.children, false);

                /**
                 * Check if the child node has no children (is not yet loaded)
                 */
                if (childNode.children && childNode.children.length === 0
                    && childNode.original && !childNode.original.lastNode) {
                    $.ajax({
                        url: options.dataUrl,
                        data: {
                            id: childNode.id,
                            selected: options.jsFormObject.updateElement.value
                        },
                        dataType: 'json',
                        success: function (response) {
                            handleSuccessResponse(response, childNode, data);
                        },
                        error: function (jqXHR, status, error) {
                            console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
                        }
                    });
                }
            }
        });

        /**
         * Event handler for 'changed.jstree' event
         */
        treeId.on('changed.jstree', function (e, data) {
            if (data.action === 'ready') {
                return;
            }
            const clickedNodeID = data.node.id, currentCheckedNodes = data.instance.get_checked();

            if (data.action === 'select_node' && !checkedNodes.includes(clickedNodeID)) {
                checkedNodes = currentCheckedNodes;
            } else if (data.action === 'deselect_node') {
                checkedNodes = currentCheckedNodes.filter((nodeID) => nodeID !== clickedNodeID);
            }
            checkedNodes.sort((a, b) => a - b);

            /**
             * Update the value of the corresponding form element with the checked node IDs
             */
            options.jsFormObject.updateElement.value = checkedNodes.join(', ');
        });
    };
});
