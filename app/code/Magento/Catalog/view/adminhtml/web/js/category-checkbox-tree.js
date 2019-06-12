/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
/* global Ext, varienWindowOnload, varienElementMethods */

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
        /* eslint-disable */
        /**
         * Fix ext compatibility with prototype 1.6
         */
        Ext.lib.Event.getTarget = function (e) {// eslint-disable-line no-undef
=======
        /**
         * Fix ext compatibility with prototype 1.6
         */
        Ext.lib.Event.getTarget = function (e) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            var ee = e.browserEvent || e;

            return ee.target ? Event.element(ee) : null;
        };

        /**
         * @param {Object} el
<<<<<<< HEAD
         * @param {Object} config
         */
        Ext.tree.TreePanel.Enhanced = function (el, config) {// eslint-disable-line no-undef
            Ext.tree.TreePanel.Enhanced.superclass.constructor.call(this, el, config);// eslint-disable-line no-undef
        };

        Ext.extend(Ext.tree.TreePanel.Enhanced, Ext.tree.TreePanel, {// eslint-disable-line no-undef
            /* eslint-enable */
            /**
             * @param {Object} config
             * @param {Boolean} firstLoad
             */
            loadTree: function (config, firstLoad) {// eslint-disable-line no-shadow
                parameters = config.parameters,
                    data = config.data,
                    root = new Ext.tree.TreeNode(parameters);// eslint-disable-line no-undef

                if (typeof parameters.rootVisible != 'undefined') {
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
            var categoryLoader = new Ext.tree.TreeLoader({// eslint-disable-line no-undef
=======
            var categoryLoader = new Ext.tree.TreeLoader({
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
             * @param {Object} config
             * @returns {Object}
             */
            categoryLoader.createNode = function (config) {// eslint-disable-line no-shadow
                var node;

                config.uiProvider = Ext.tree.CheckboxNodeUI;// eslint-disable-line no-undef

                if (config.children && !config.children.length) {
                    delete config.children;
                    node = new Ext.tree.AsyncTreeNode(config);// eslint-disable-line no-undef
                } else {
                    node = new Ext.tree.TreeNode(config);// eslint-disable-line no-undef
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                }

                return node;
            };

            /**
             * @param {Object} parent
<<<<<<< HEAD
             * @param {Object} config
             * @param {Integer} i
             */
            categoryLoader.processCategoryTree = function (parent, config, i) {// eslint-disable-line no-shadow
                var node,
                    _node = {};

                config[i].uiProvider = Ext.tree.CheckboxNodeUI;// eslint-disable-line no-undef

                _node = Object.clone(config[i]);

                if (_node.children && !_node.children.length) {
                    delete _node.children;
                    node = new Ext.tree.AsyncTreeNode(_node);// eslint-disable-line no-undef
                } else {
                    node = new Ext.tree.TreeNode(config[i]);// eslint-disable-line no-undef
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                }
                parent.appendChild(node);
                node.loader = node.getOwnerTree().loader;

                if (_node.children) {
                    categoryLoader.buildCategoryTree(node, _node.children);
                }
            };

            /**
             * @param {Object} parent
<<<<<<< HEAD
             * @param {Object} config
             * @returns {void}
             */
            categoryLoader.buildCategoryTree = function (parent, config) {// eslint-disable-line no-shadow
                var i = 0;

                if (!config) {
                    return null;
                }

                if (parent && config && config.length) {
                    for (i; i < config.length; i++) {
                        categoryLoader.processCategoryTree(parent, config, i);
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    }
                }
            };

            /**
             *
             * @param {Object} hash
             * @param {Object} node
             * @returns {Object}
             */
<<<<<<< HEAD
            categoryLoader.buildHashChildren = function (hash, node) {// eslint-disable-line no-shadow
                var i = 0,
                    len;

                // eslint-disable-next-line no-extra-parens
                if ((node.childNodes.length > 0) || (node.loaded === false && node.loading === false)) {
                    hash.children = [];

                    for (i, len = node.childNodes.length; i < len; i++) {
                        /* eslint-disable */
                        if (!hash.children) {
                            hash.children = [];
                        }
                        /* eslint-enable */
=======
            categoryLoader.buildHashChildren = function (hash, node) {
                var i = 0,
                    len;

                if (node.childNodes.length > 0 || node.loaded === false && node.loading === false) {
                    hash.children = [];

                    for (i, len = node.childNodes.length; i < len; i++) {
                        hash.children = hash.children ? hash.children : [];
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
            /* eslint-disable */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
            /* eslint-enable */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        });
    };
});
