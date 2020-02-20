/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Ext, varienWindowOnload, varienElementMethods */

define([
    'jquery',
    'prototype',
    'extjs/ext-tree-checkbox',
    'mage/adminhtml/form'
], function (jQuery) {
    'use strict';

    return function (config) {
        var tree,
            options = {
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
            data = {},
            parameters = {},
            root = {},
            key = '';

        /**
         * Fix ext compatibility with prototype 1.6
         */
        Ext.lib.Event.getTarget = function (e) {
            var ee = e.browserEvent || e;

            return ee.target ? Event.element(ee) : null;
        };

        /**
         * @param {Object} el
         * @param {Object} nodeConfig
         */
        Ext.tree.TreePanel.Enhanced = function (el, nodeConfig) {
            Ext.tree.TreePanel.Enhanced.superclass.constructor.call(this, el, nodeConfig);
        };

        Ext.extend(Ext.tree.TreePanel.Enhanced, Ext.tree.TreePanel, {
            /**
             * @param {Object} treeConfig
             * @param {Boolean} firstLoad
             */
            loadTree: function (treeConfig, firstLoad) {
                parameters = treeConfig.parameters,
                    data = treeConfig.data,
                    root = new Ext.tree.TreeNode(parameters);

                if (typeof parameters.rootVisible !== 'undefined') {
                    this.rootVisible = parameters.rootVisible * 1;
                }

                this.nodeHash = {};
                this.setRootNode(root);

                if (firstLoad) {
                    this.addListener('click', this.categoryClick.createDelegate(this));
                }

                this.loader.buildCategoryTree(root, data);
                this.el.dom.innerHTML = '';
                // render the tree
                this.render();
            },

            /**
             * @param {Object} node
             */
            categoryClick: function (node) {
                node.getUI().check(!node.getUI().checked());
            }
        });

        jQuery(function () {
            var categoryLoader = new Ext.tree.TreeLoader({
                dataUrl: config.dataUrl
            });

            /**
             * @param {Object} response
             * @param {Object} parent
             * @param {Function} callback
             */
            categoryLoader.processResponse = function (response, parent, callback) {
                config = JSON.parse(response.responseText);

                this.buildCategoryTree(parent, config);

                if (typeof callback === 'function') {
                    callback(this, parent);
                }
            };

            /**
             * @param {Object} nodeConfig
             * @returns {Object}
             */
            categoryLoader.createNode = function (nodeConfig) {
                var node;

                nodeConfig.uiProvider = Ext.tree.CheckboxNodeUI;

                if (nodeConfig.children && !nodeConfig.children.length) {
                    delete nodeConfig.children;
                    node = new Ext.tree.AsyncTreeNode(nodeConfig);
                } else {
                    node = new Ext.tree.TreeNode(nodeConfig);
                }

                return node;
            };

            /**
             * @param {Object} parent
             * @param {Object} nodeConfig
             * @param {Integer} i
             */
            categoryLoader.processCategoryTree = function (parent, nodeConfig, i) {
                var node,
                    _node = {};

                nodeConfig[i].uiProvider = Ext.tree.CheckboxNodeUI;

                _node = Object.clone(nodeConfig[i]);

                if (_node.children && !_node.children.length) {
                    delete _node.children;
                    node = new Ext.tree.AsyncTreeNode(_node);
                } else {
                    node = new Ext.tree.TreeNode(nodeConfig[i]);
                }
                parent.appendChild(node);
                node.loader = node.getOwnerTree().loader;

                if (_node.children) {
                    categoryLoader.buildCategoryTree(node, _node.children);
                }
            };

            /**
             * @param {Object} parent
             * @param {Object} nodeConfig
             * @returns {void}
             */
            categoryLoader.buildCategoryTree = function (parent, nodeConfig) {
                var i = 0;

                if (!nodeConfig) {
                    return null;
                }

                if (parent && nodeConfig && nodeConfig.length) {
                    for (i; i < nodeConfig.length; i++) {
                        categoryLoader.processCategoryTree(parent, nodeConfig, i);
                    }
                }
            };

            /**
             *
             * @param {Object} hash
             * @param {Object} node
             * @returns {Object}
             */
            categoryLoader.buildHashChildren = function (hash, node) {
                var i = 0,
                    len;

                if (node.childNodes.length > 0 || node.loaded === false && node.loading === false) {
                    hash.children = [];

                    for (i, len = node.childNodes.length; i < len; i++) {
                        hash.children = hash.children ? hash.children : [];
                        hash.children.push(this.buildHash(node.childNodes[i]));
                    }
                }

                return hash;
            };

            /**
             * @param {Object} node
             * @returns {Object}
             */
            categoryLoader.buildHash = function (node) {
                var hash = {};

                hash = this.toArray(node.attributes);

                return categoryLoader.buildHashChildren(hash, node);
            };

            /**
             * @param {Object} attributes
             * @returns {Object}
             */
            categoryLoader.toArray = function (attributes) {
                data = {};

                for (key in attributes) {

                    if (attributes[key]) {
                        data[key] = attributes[key];
                    }
                }

                return data;
            };

            categoryLoader.on('beforeload', function (treeLoader, node) {
                treeLoader.baseParams.id = node.attributes.id;
                treeLoader.baseParams.selected = options.jsFormObject.updateElement.value;
            });

            categoryLoader.on('load', function () {
                varienWindowOnload();
            });

            tree = new Ext.tree.TreePanel.Enhanced(options.divId, {
                animate: false,
                loader: categoryLoader,
                enableDD: false,
                containerScroll: true,
                selModel: new Ext.tree.CheckNodeMultiSelectionModel(),
                rootVisible: options.rootVisible,
                useAjax: options.useAjax,
                currentNodeId: options.currentNodeId,
                addNodeTo: false,
                rootUIProvider: Ext.tree.CheckboxNodeUI
            });

            tree.on('check', function (node) {
                options.jsFormObject.updateElement.value = this.getChecked().join(', ');
                varienElementMethods.setHasChanges(node.getUI().checkbox);
            }, tree);

            // set the root node
            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            parameters = {
                text: options.name,
                draggable: false,
                checked: options.checked,
                uiProvider: Ext.tree.CheckboxNodeUI,
                allowDrop: options.allowDrop,
                id: options.rootId,
                expanded: options.expanded,
                category_id: options.categoryId
            };
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            tree.loadTree({
                parameters: parameters, data: options.treeJson
            }, true);
        });
    };
});
