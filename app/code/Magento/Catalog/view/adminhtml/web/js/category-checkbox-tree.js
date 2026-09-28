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
                treeJson: config.treeJson
            },
            initialSelection = [];

        /**
         * Get the jstree element by its ID
         */
        const treeId = $('#' + options.divId);

        /**
         * @return {Element}
         */
        function getTargetInput() {
            return options.jsFormObject.updateElement;
        }

        /**
         * Recursively marks nodes which children are not loaded.
         *
         * @param {Array} nodes
         * @returns {Array}
         */
        function prepareNodes(nodes) {
            return nodes.map(
                function (node) {
                    let obj = {...node, state: {}};

                    if (Array.isArray(obj.children)) {
                        if (obj.children.length > 0) {
                            obj.children = prepareNodes(obj.children);
                        } else {
                            obj.children = true;
                        }
                    }

                    if (obj.expanded) {
                        obj.state.opened = true;
                    }

                    if (initialSelection.includes(obj.id)) {
                        obj.state.selected = true;
                    }

                    return obj;
                }
            );
        }

        /**
         * Load the node and execute the callback function
         *
         * @param {Object} node
         * @param {Function} callback
         */
        function load(node, callback) {
            let target = getTargetInput(),
                instance = this;

            if (node.id === $.jstree.root) {
                callback.call(instance, prepareNodes(options.treeJson));
            } else if (Array.isArray(node.children) && node.children.length === 0) {
                $.ajax({
                    url: options.dataUrl,
                    data: {
                        id: node.id,
                        selected: target.value
                    },
                    dataType: 'json',
                    success: function (response) {
                        callback.call(instance, prepareNodes(response));
                    },
                    error: function (jqXHR, status, error) {
                        console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
                    }
                });
            } else {
                callback.call(instance, false);
            }
        }

        /**
         * Event handler for 'init.jstree' event
         */
        treeId.on('init.jstree', function () {
            let target = getTargetInput();

            initialSelection = target.value ? target.value.split(',').map(id => id.trim()) : [];
        });

        /**
         * Event handler for 'changed.jstree' event
         */
        treeId.on('changed.jstree', function (e, data) {
            if (data.action === 'ready') {
                return;
            }

            /**
             * Update the value of the corresponding form element with the checked node IDs
             *
             * keep the checked nodes that are not in the tree yet,
             * and merge them with the currently checked nodes
             * then sort the resulted array
             */
            let target = getTargetInput(),
                selected = initialSelection
                    .filter(node => data.instance.get_node(node) === false)
                    .concat(data.instance.get_checked());

            target.value = [...new Set(selected)].sort((a, b) => a - b).join(',');
        });

        /**
         * Initialize the jstree with configuration options
         */
        treeId.jstree({
            core: {
                data: load,
                check_callback: true
            },
            plugins: ['checkbox'],
            checkbox: {
                three_state: false
            }
        });
    };
});
