/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

        /* eslint-disable */
        /**
         * Fix ext compatibility with prototype 1.6
         */
        Ext.lib.Event.getTarget = function (e) {// eslint-disable-line no-undef
            var ee = e.browserEvent || e;

            return ee.target ? Event.element(ee) : null;
        };

        /**
         * @param {Object} el
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
            var categoryLoader = new Ext.tree.TreeLoader({// eslint-disable-line no-undef
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
                }

                return node;
            };

            /**
             * @param {Object} parent
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
                }
                parent.appendChild(node);
                node.loader = node.getOwnerTree().loader;

                if (_node.children) {
                    categoryLoader.buildCategoryTree(node, _node.children);
                }
            };

            /**
             * @param {Object} parent
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
                    }
                }
            };

            /**
             *
             * @param {Object} hash
             * @param {Object} node
             * @returns {Object}
             */
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

            /* eslint-disable */
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
            /* eslint-enable */
        });
    };
});
