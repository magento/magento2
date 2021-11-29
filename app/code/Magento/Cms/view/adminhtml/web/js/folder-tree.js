/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui',
    'jquery/jstree/jquery.jstree'
], function ($) {
    'use strict';

    $.widget('mage.folderTree', {
        options: {
            root: 'root',
            rootName: 'Root',
            url: '',
            currentPath: ['root'],
            tree: {
                core: {
                    themes: {
                        dots: false
                    },
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    check_callback: true
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                }
            }
        },

        /** @inheritdoc */
        _create: function () {
            var options = this.options,
                treeOptions = $.extend(
                    true,
                    {},
                    options.tree,
                    {
                        core: {
                            data: {
                                url: options.url,
                                type: 'POST',
                                dataType: 'text',
                                dataFilter: $.proxy(function (data) {
                                    return this._convertData(JSON.parse(data));
                                }, this),

                                /**
                                 * @param {HTMLElement} node
                                 * @return {Object}
                                 */
                                data: function (node) {
                                    return {
                                        node: node.id === 'root' ? null : node.id,
                                        'form_key': window.FORM_KEY
                                    };
                                }
                            }
                        }
                    }
                );

            this.element.jstree(treeOptions)
                .on('ready.jstree', $.proxy(this.treeLoaded, this))
                .on('load_node.jstree', $.proxy(this._createRootNode, this));
        },

        /**
         * Tree loaded.
         */
        treeLoaded: function () {
            var path = this.options.currentPath,
                tree = this.element,
                lastExistentFolderEl,

                /**
                 * Recursively open folders specified in path array.
                 */
                recursiveOpen = function () {
                    var folderEl = $('[data-id="' + path.pop() + '"]');

                    // if folder doesn't exist, select the last opened folder
                    if (!folderEl.length) {
                        tree.jstree('select_node', lastExistentFolderEl);

                        return;
                    }

                    lastExistentFolderEl = folderEl;

                    if (path.length) {
                        tree.jstree('open_node', folderEl, recursiveOpen);
                    } else {
                        tree.jstree('open_node', folderEl, function () {
                            tree.jstree('select_node', folderEl);
                        });
                    }
                };

            recursiveOpen();
        },

        /**
         * Create tree root node
         *
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _createRootNode: function (event, data) {
            var rootNode, children;

            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            if (data.node.id === '#') {
                rootNode = {
                    id: this.options.root,
                    text: this.options.rootName,
                    li_attr: {
                        'data-id': this.options.root
                    }
                };
                children = data.node.children;

                data.instance.element.jstree().create_node(null, rootNode, 'first', function () {
                    data.instance.element.jstree().move_node(children, rootNode.id);
                });
            }
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        },

        /**
         * @param {*} data
         * @return {*}
         * @private
         */
        _convertData: function (data) {
            return $.map(data, function (node) {

                return {
                    id: node.id,
                    text: node.text,
                    path: node.path,
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    li_attr: {
                        'data-id': node.id
                    },
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    children: node.children
                };
            });
        }
    });

    return $.mage.folderTree;
});
