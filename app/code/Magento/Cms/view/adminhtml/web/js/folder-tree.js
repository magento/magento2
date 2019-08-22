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
                'plugins': ['themes', 'json_data', 'ui', 'hotkeys'],
                'themes': {
                    'theme': 'default',
                    'dots': false,
                    'icons': true
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
                        'json_data': {
                            data: {
                                data: options.rootName,
                                state: 'closed',
                                metadata: {
                                    node: {
                                        id: options.root,
                                        text: options.rootName
                                    }
                                },
                                attr: {
                                    'data-id': options.root,
                                    id: options.root
                                }
                            },
                            ajax: {
                                url: options.url,

                                /**
                                 * @param {Object} node
                                 * @return {Object}
                                 */
                                data: function (node) {
                                    return {
                                        node: node.data('id'),
                                        'form_key': window.FORM_KEY
                                    };
                                },
                                success: this._convertData
                            }
                        }
                    }
                );

            this.element.jstree(treeOptions).on('loaded.jstree', $.proxy(this.treeLoaded, this));
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
         * @param {*} data
         * @return {*}
         * @private
         */
        _convertData: function (data) {
            return $.map(data, function (node) {
                var codeCopy = $.extend({}, node);

                return {
                    data: node.text,
                    attr: {
                        'data-id': node.id,
                        id: node.id
                    },
                    metadata: {
                        node: codeCopy
                    },
                    state: node.state || 'closed'
                };
            });
        }
    });

    return $.mage.folderTree;
});
